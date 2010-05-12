<?php
namespace zing\db;

class Migrator
{
    public static function application_migration_dir() {
        return ZING_ROOT . '/db/migrations';
    }
    
    protected $db;
    protected $migrations = null;
    
    public function __construct() {
        $this->db = \GDB::instance();
    }
    
    public function has_outstanding_migrations() {
        $this->preflight();
        return $this->migrations()->has_outstanding();
    }
    
    public function run_outstanding_migrations() {
        $this->preflight();
        foreach ($this->migrations()->outstanding() as $ms) {
            $ms->up();
            $this->migration_applied($ms);
        }
    }
    
    protected function migrations() {
        if ($this->migrations === null) {
            $locator = new MigrationLocator;
            $locator->set_applied_migrations($this->applied_migrations());
            $this->migrations = $locator->locate_migrations();
        }
        return $this->migrations;
    }
    
    protected function preflight() {
        $this->create_migration_table();
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
    
    protected function applied_migrations() {
        $applied = array();
        foreach ($this->db->q("SELECT * FROM zing_migration") as $row) {
            $applied["{$row['migration_source']}_{$row['migration_id']}"] = true;
        }
        return $applied;
    }
    
    protected function migration_applied(MigrationStub $ms) {
        $this->db->x("
            INSERT INTO zing_migration
                (migration_source, migration_id)
            VALUES
                ({s}, {i})
        ", $ms->source, $ms->timestamp);
    }
    
    protected function migration_unapplied(MigrationStub $ms) {
        $this->db->x("
            DELETE FROM zing_migration
            WHERE
                migration_source = {s} AND
                migration_id = {i}
        ", $ms->source, $ms->timestamp);
    }
}

class MigrationLocator
{
    protected $applied_migrations;
    
    public function set_applied_migrations(array $applied_migrations) {
        $this->applied_migrations = $applied_migrations;
    }
    
    public function locate_migrations() {
        $list  = new MigrationList;
        foreach ($this->sources() as $source => $path) {
            foreach (glob($path . '/*.php') as $file) {
                if (preg_match('|/(\d+)_(\w+)\.php$|', $file, $matches)) {
                    $stub = new MigrationStub;
                    $stub->source           = $source;
                    $stub->path             = $file;
                    $stub->timestamp        = (int) $matches[1];
                    $stub->migration_name   = $matches[2];
                    $stub->applied          = isset($this->applied_migrations["{$stub->source}_{$stub->timestamp}"]);
                    $list->add($stub);
                }
            }
        }
        $list->sort();
        return $list;
    }
    
    // Returns a list of all migration sources
    // In the future we can add plugin migrations in here, e.g.
    // 'plugin.cms' => PATH_TO_PLUGIN_MIGRATION_DIR
    public function sources() {
        return array(
            'app'       => Migrator::application_migration_dir()
        );
    }
}

// superload-ignore
class MigrationList
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
    
    public function outstanding() {
        return array_filter($this->migrations, function($i) { return !$i->applied; });
    }
}

// superload-ignore
class MigrationStub
{
    public $source;
    public $path;
    public $timestamp;
    public $migration_name;
    public $applied;
    
    public function up() {
        $this->create()->up();
    }
    
    public function down() {
        $this->create()->down();
    }
    
    public function create() {
        require_once $this->path;
        $migration_class = \Inflector::camelize($this->migration_name);
        return new $migration_class;
    }
}

class Migration extends \gdb\Migration
{
}
?>