<?php
namespace zing\db;

class MigrationSupport
{
    public static function create_migration_table() {
        
        $db = \GDB::instance();
        $schema = $db->new_schema_builder();
        
        if (!$schema->table_exists('zing_migration')) {
            $def = new \gdb\TableDefinition('zing_migrations', array('no_id' => true));
            $def->integer('migration_id', array('null' => false));
            $def->string('migration_source', array('null' => false));
            $def->set_primary_key('migration_id', 'migration_source');
            $schema->create_table($def);
        }
        
    }
}

class MigrationStub
{
    public function __construct() {
        
    }
}
?>