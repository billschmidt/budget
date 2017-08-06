<?php
/**
 * Bill Schmidt
 * Date: 8/6/2017
 * Time: 12:45 PM
 */

namespace BillBudget\Fields;


class MoneyField extends FloatField {
    public function install_field() {
        return '"' . $this->name . '" decimal(10,2)' . ($this->required ? ' not null' : '');
    }

    public function get_value() {
        return money_format('%.2n', $this->value);
    }
}