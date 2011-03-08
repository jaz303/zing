<?php
namespace zing\db;

class AmbiguousMigrationException extends \Exception {}
class MigrationNotFoundException extends \Exception {}

class Migrator
{
    public static function application_migration_dir() {
        return ZING_ROOT . '/db/migrations';
    }
    
    protected $db;
    protected $migrations           = null;
    protected $applied_migrations;
    
    public function __construct() {
        $this->db = \GDB::instance();
        $this->create_migration_table();
    }
    
    //
    // Public interface
    
    public function get_migrations() { return $this->migrations(); }
    public function has_outstanding_migrations() { return $this->migrations()->has_outstanding(); }
    public function get_outstanding_migrations() { return $this->migrations()->outstanding(); }
    
    public function find_one($source, $timestamp_or_name) {
        return $this->migrations()->find_one($source, $timestamp_or_name);
    }
    
    public function find_applied_migrations_after($source, $timestamp_or_name) {
        return $this->migrations()->find_applied_migrations_after($source, $timestamp_or_name);
    }
    
    //
    //
    
    public function is_applied(MigrationStub $ms) {
        return $this->db->q("
            SELECT 1 FROM zing_migration WHERE migration_source = {s} AND migration_id = {i}
        ", $ms->source, $ms->timestamp)->first_row() != false;
    }
    
    public function mark_as_applied(MigrationStub $ms) {
        $this->db->x("
            REPLACE INTO zing_migration
                (migration_source, migration_id)
            VALUES
                ({s}, {i})
        ", $ms->source, $ms->timestamp);
    }
    
    public function mark_as_unapplied(MigrationStub $ms) {
        $this->db->x("
            DELETE FROM zing_migration
            WHERE
                migration_source = {s} AND
                migration_id = {i}
        ", $ms->source, $ms->timestamp);
    }
    
    //
    //
    
    protected function migrations() {
        if ($this->migrations === null) {
            $locator = new MigrationLocator($this);
            $this->migrations = $locator->locate_migrations();
            $this->migrations->sort();
        }
        return $this->migrations;
    }
    
    protected function create_migration_table() {
        $schema = $this->db->new_schema_builder();
        if (!$schema->table_exists('zing_migration')) {
            $def = new \gdb\TableDefinition('zing_migration', array('no_id' => true));
            $def->string('migration_source', array('null' => false));
            $def->integer('migration_id', array('null' => false));
            $def->set_primary_key('migration_id', 'migration_source');
            $schema->create_table($def);
        }
    }
}

/**
 * A MigrationLocator locates migrations. Hurr.
 *
 * In the future it will be possible to provide an alternative locator via a
 * configuration option, for circumstances where people want to flip and twist
 * the framework.
 */
class MigrationLocator
{
    private $migrator;
    
    public function __construct(Migrator $migrator) {
        $this->migrator = $migrator;
    }
    
    /**
     * Locate migrations and return a populated MigrationList
     */
    public function locate_migrations() {
        $list  = new MigrationList;
        foreach ($this->sources() as $source => $path) {
            foreach (glob($path . '/*.php') as $file) {
                if (preg_match('|/(\d+)_(\w+)\.php$|', $file, $matches)) {
                    $stub = new MigrationStub($this->migrator);
                    $stub->source           = $source;
                    $stub->path             = $file;
                    $stub->timestamp        = (int) $matches[1];
                    $stub->migration_name   = $matches[2];
                    $stub->class_name       = \zing\lang\Introspector::first_class_in_file($file);
                    $list->add($stub);
                }
            }
        }
        return $list;
    }
    
    protected function sources() {
        $sources = array('app' => Migrator::application_migration_dir());
        $plugin_manager = \zing\plugin\Manager::instance();
        foreach ($plugin_manager->plugins() as $plugin) {
            if ($plugin->has_migrations()) {
                $sources["plugin.{$plugin->id()}"] = $plugin->migration_path();
            }
         }
         return $sources;   
    }
}

// zing-autoload-ignore
class MigrationList implements \IteratorAggregate, \Countable
{
    private $migrations = array();
    
    public function add(MigrationStub $ms) {
        $this->migrations[] = $ms;
    }
    
    public function sort() {
        usort($this->migrations, function($l, $r) { return $l->timestamp - $r->timestamp; });
    }
    
    public function has_outstanding() {
        return count($this->outstanding()) > 0;
    }
    
    public function find_one($source, $timestamp_or_name) {
        $out = array();
        list($timestamp, $name) = $this->resolve_timestamp_and_name($timestamp_or_name);
        foreach ($this->migrations as $migration) {
            if ($source && strcmp($migration->source, $source) != 0) {
                continue;
            }
            if (($timestamp !== null && $migration->timestamp == $timestamp) ||
                ($name !== null && $migration->migration_name == $name)) {
                $out[] = $migration;
            }
        }
        if (empty($out)) {
            throw new MigrationNotFoundException;
        } elseif (count($out) > 1) {
            throw new AmbiguousMigrationException;
        } else {
            return $out[0];
        }
    }
    
    public function find_applied_migrations_after($source, $timestamp_or_name) {
        $out = array();
        list($timestamp, $name) = $this->resolve_timestamp_and_name($timestamp_or_name);
        foreach (array_reverse($this->migrations) as $migration) {
            if (strcmp($source, $migration->source) != 0) {
                continue;
            }
            if (!$migration->is_applied()) {
                continue;
            }
            if (($timestamp !== null && $migration->timestamp <= $timestamp) ||
                ($name !== null && $migration->migration_name == $name)) {
                break;
            }
            $out[] = $migration;
        }
        return array_reverse($out);
    }
    
    public function outstanding() {
        return array_filter($this->migrations, function($i) { return !$i->is_applied(); });
    }
    
    public function getIterator() {
        return new \ArrayIterator($this->migrations);
    }
    
    public function count() {
        return count($this->migrations);
    }
    
    private function resolve_timestamp_and_name($timestamp_or_name) {
        $timestamp = $name = null;
        if (preg_match('/^\d+$/', $timestamp_or_name)) {
            $timestamp = (int) $timestamp_or_name;
        } else {
            $name = $timestamp_or_name;
        }
        return array($timestamp, $name);
    }
}

// zing-autoload-ignore
class MigrationStub
{
    private $migrator;
    
    public $source;             // 'app' or plugin ID
    public $path;               // full path to plugin file
    public $timestamp;          // timestamp
    public $migration_name;     // name
    public $class_name;         // class name
    
    public function __construct(Migrator $migrator) {
        $this->migrator = $migrator;
    }
    
    public function up() {
        $this->create()->up();
        $this->migrator->mark_as_applied($this);
    }
    
    public function down() {
        $this->create()->down();
        $this->migrator->mark_as_unapplied($this);
    }
    
    public function create() {
        require_once $this->path;
        $migration_class = $this->class_name;
        return new $migration_class;
    }
    
    public function is_applied() {
        return $this->migrator->is_applied($this);
    }
    
    public function toString() {
        return "{$this->timestamp} {$this->migration_name} ({$this->source})";
    }
}

class Migration extends \gdb\Migration
{
}
?>