<?php
/**
 * Bill Schmidt
 * Date: 8/6/2017
 * Time: 12:21 PM
 */

namespace BillBudget;

/**
 * Class Field
 * @package BillBudget
 */
abstract class Field {
    public $name;
    public $readable_name;
    public $table;
    public $value;
    public $set;
    public $visible;
    public $required;
    public $options;

    public function __construct($name, $readable_name, $table = '', $options = []) {
        $this->name = $name;
        $this->readable_name = $readable_name;
        $this->table = $table;
        $this->required = !empty($options['required']) ? $options['required'] : false;
        $this->options = $options;
        $this->visible = true;
    }

    /**
     * @return string
     */
    public function db_field() {
        return '"' . $this->table . '"."' . $this->name . '"';
    }

    /**
     * @param $value
     * @return bool
     */
    abstract public function valid($value);

    /**
     * @param $value
     * @return bool
     */
    abstract public function set_value($value);

    /**
     * @return mixed
     */
    public function get_value() {
        return $this->value;
    }

    /**
     * @return string
     */
    abstract public function install_field();

    public function filter() {

    }
}