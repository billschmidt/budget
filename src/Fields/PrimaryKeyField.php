<?php
/**
 * Bill Schmidt
 * Date: 8/6/2017
 * Time: 12:40 PM
 */

namespace BillBudget\Fields;

/**
 * Class PrimaryKeyField
 * @package BillBudget\Fields
 */
class PrimaryKeyField extends IntField {

    public function install_field() {
        return '"' . $this->name . '" serial not null';
    }

}