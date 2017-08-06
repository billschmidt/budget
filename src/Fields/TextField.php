<?php
/**
 * Bill Schmidt
 * Date: 8/6/2017
 * Time: 12:50 PM
 */

namespace BillBudget\Fields;

use BillBudget\Field;

/**
 * Class TextField
 * @package BillBudget\Fields
 */
class TextField extends Field {

    /**
     * @param $value
     * @return bool
     */
    public function valid($value) {
        if ($this->required && strlen($value) == 0) {
            return false;
        } else {
            return true;
        }
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
        return '"' . $this->name . '" text' . ($this->required ? ' not null' : '');
    }
}