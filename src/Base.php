<?php
/**
 * Bill Schmidt
 * Date: 8/6/2017
 * Time: 12:14 PM
 */

namespace BillBudget;

use BillBudget\Exceptions\InvalidField;
use BillBudget\Fields\PrimaryKeyField;

/**
 * Class Base
 * @package BillBudget
 */
abstract class Base {
    const CLASS_KEY = 'base';
    const READABLE_NAME = 'Base';

    /** @var  Field[] */
    protected $_fields;

    public static function get_fields() {
        return [
            'id' => new PrimaryKeyField('id', 'ID', static::CLASS_KEY),
        ];
    }

    public function __construct($data) {
        $this->_fields = static::get_fields();


    }

    /**
     * @param $name
     * @return mixed
     * @throws InvalidField
     */
    public function __get($name) {
        // get a field value
        if (isset($this->_fields[$name])) {
            return $this->_fields[$name]->get_value();
        } else {
            throw new InvalidField("Can't get field: " . $name);
        }
    }

    /**
     * @param $name
     * @param $value
     * @throws InvalidField
     */
    public function __set($name, $value) {
        if (array_key_exists($name, $this->_fields)) {
            if (!$this->_fields[$name]->set_value($value)) {
                throw new InvalidField("Can't set field: " . $name);
            }
        }
    }

    public function __isset($name) {
        return array_key_exists($name, $this->_fields) && $this->_fields[$name]->set;
    }

    public function __unset($name) {
        if(array_key_exists($name, $this->_fields)) {
            $this->_fields[$name]->set = false;
            $this->_fields[$name]->value = null;
        }
    }

}