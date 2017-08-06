<?php
/**
 * Bill Schmidt
 * Date: 8/6/2017
 * Time: 12:20 PM
 */

namespace BillBudget\Fields;

use BillBudget\Field;

/**
 * Class IntField
 * @package BillBudget\Fields
 */
class IntField extends Field {

    /**
     * @param $value
     * @return bool
     */
    public function valid($value) {
        if (!$this->required && ($value === false || strlen($value) == 0 || $value === null)) return true;

        return is_numeric($value);
    }

    /**
     * @param $value
     * @return bool
     */
    public function set_value($value) {
        if ($this->valid($value)) {
            $this->value = $value;
            $this->set = true;
        } else {
            $this->value = null;
            $this->set = false;
        }
        return $this->set;
    }

    /**
     * @return string
     */
    public function install_field() {
        return '"' . $this->name . '" int' . ($this->required ? ' not null' : '');
    }
}