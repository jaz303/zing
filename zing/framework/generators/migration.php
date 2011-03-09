<?php
namespace zing\generator;

class MigrationGenerator extends \zing\generator\Generator
{
    public function description() {
        return "Create a new database migration";
    }
    
    /**
     * @todo need to check for duplicate migration names
     */
    protected function parse_args(array $args) {
        if (count($args) != 1) {
            throw new \InvalidArgumentException("Usage: script/generate migration migration_name");
        }
        
        $this->migration_name           = $args[0];
        $this->migration_class_prefix   = \Inflector::camelize($this->migration_name);
        
        $now = new \Date_Time;
        $this->utc_timestamp = $now->to_utc()->timestamp();
    }

    protected function manifest() {
        return array(
            ('db/migrations/' . $this->utc_timestamp . '_' . $this->migration_name . '.php')
                => $this->relative_path('/templates/migration_template.php')
        );
    }
}
?>