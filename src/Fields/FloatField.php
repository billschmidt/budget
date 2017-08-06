<?php
/**
 * Bill Schmidt
 * Date: 8/6/2017
 * Time: 12:43 PM
 */

namespace BillBudget\Fields;

/**
 * Class FloatField
 * @package BillBudget\Fields
 */
class FloatField extends IntField {

    /**
     * @return string
     */
    public function install_field() {
        return '"' . $this->name . '" double precision' . ($this->required ? ' not null' : '');
    }
}