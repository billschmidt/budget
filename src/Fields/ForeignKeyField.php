<?php
/**
 * Bill Schmidt
 * Date: 8/24/2017
 * Time: 9:39 AM
 */

namespace BillBudget\Fields;


use BillBudget\Base;

class ForeignKeyField extends IntField {
    /**
     * ForeignKeyField constructor.
     *
     * class_reference, required option
     *
     * @param $name
     * @param $readable_name
     * @param string $table
     * @param array $options
     * @throws \Exception
     */
    public function __construct($name, $readable_name, $table = '', $options = []) {
        if (empty($options['class_reference'])) {
            throw new \Exception("Foreign key field $name missing a class reference.");
        }
        parent::__construct($name, $readable_name, $table, $options);
    }


    /**
     * @return string
     */
    public function install_field() {
        /** @var Base $fk_class */
        $fk_class = $this->options['class_reference'];

        $field = '"' . $this->name . '" int' . ($this->required ? ' not null' : '');

        if ($this->required) {
            $on_delete = 'cascade';
        } else {
            $on_delete = 'set null';
        }

        $table = $fk_class::CLASS_KEY;
        $primary = '"id"';
        foreach($fk_class::get_fields() as $field) {
            if ($field instanceof PrimaryKeyField) {
                $primary = '"' . $field->name . '"';
            }
        }

        $field .= ' references "' . $table . '" (' . $primary . ') on delete ' . $on_delete;

        return $field;
    }

    /**
     * @return Base|null
     */
    public function get_reference() {
        if ($this->value != null) {
            /** @var Base $fk_class */
            $fk_class = $this->options['class_reference'];

            return new $fk_class($this->value);
        } else {
            return $this->value;
        }
    }
}