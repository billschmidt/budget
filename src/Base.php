<?php
/**
 * Bill Schmidt
 * Date: 8/6/2017
 * Time: 12:14 PM
 */

namespace BillBudget;

use BillBudget\Database\DB;
use BillBudget\Database\Query;
use BillBudget\Exceptions\InsertError;
use BillBudget\Exceptions\InvalidField;
use BillBudget\Exceptions\RecordNotFound;
use BillBudget\Fields\PrimaryKeyField;

/**
 * Class Base
 * @package BillBudget
 */
abstract class Base implements \JsonSerializable {
    const CLASS_KEY = 'base';
    const READABLE_NAME = 'Base';

    /** @var  Field[] */
    protected $_fields;

    /**
     * @return string
     */
    public static function plural_name() {
        if (defined(get_called_class() . '::PLURAL_NAME')) {
            return constant(get_called_class() . '::PLURAL_NAME');
        } else {
            return static::READABLE_NAME . 's';
        }
    }

    /**
     * @return Field[]
     */
    public static function get_fields() {
        return [
            'id' => new PrimaryKeyField('id', 'ID', static::CLASS_KEY),
        ];
    }

    public function __construct($data, $options = []) {
        $this->_fields = static::get_fields();

        // data is an id, fetch it from the database
        if (is_numeric($data)) {
            $id = $data;

            $primary = '';
            $q_fields = [];
            foreach($this->_fields as $f) {
                if($f instanceof PrimaryKeyField) {
                    $primary = $f->db_field();
                } else {
                    $q_fields []= $f->db_field();
                }
            }

            $query = 'select '. implode(', ', $q_fields) .' from ' . static::CLASS_KEY . ' where ' . $primary . ' = ?';

            $stmt = DB::query($query, [$id]);

            if ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                foreach ($result as $key => $value) {
                    $this->_fields[$key]->set_value($value);
                }
            } else {
                throw new RecordNotFound(static::READABLE_NAME . ' '. $id . ' not found');
            }
        } else if (is_assoc($data)) {
            // data is an array of values
            foreach ($data as $key => $value) {
                $this->_fields[$key]->set_value($value);
            }
        } else {
            throw new \Exception('Could not initialize ' . static::READABLE_NAME . ' from ' . gettype($data));
        }
    }

    public static function create($values, $options = []) {
        $fields = static::get_fields();

        $primary_field = 'id';

        $f_count = 0;
        $q_fields = [];
        $q_values = [];
        $errors = [];

        // check all fields for errors
        foreach ($fields as $k => $f) {
            // skip the primary key
            if(!($f instanceof PrimaryKeyField)) {
                // required fields
                if ($f->required && !array_key_exists($k, $values)) {
                    $errors[$k] = $f->readable_name . ' is required';
                }

                if ($f->set_value(array_key_exists($k, $values) ? $values[$k] : null)) {
                    $q_fields []= $f->db_field();
                    $f_count++;
                    // get the raw value, not a processes get_value
                    $q_values []= $f->value;
                } else {
                    $errors[$k] = $f->readable_name . ' is invalid.';
                }
            } else {
                $primary_field = $k;
            }
        }

        if (count($errors) == 0) {
            $fs = implode(', ', $q_fields);
            $qs = array_fill(0, $f_count, '?');
            $query = 'insert into "' . static::CLASS_KEY . '" (' . $fs . ') values (' . $qs . ')';

            $stmt = DB::query($query, $q_values);

            if ($stmt->rowCount() > 0) {
                $id = DB::lastInsertId(static::CLASS_KEY . '_' . $primary_field . '_seq');
            }

        } else {
            throw new InsertError('Could not create ' . static::READABLE_NAME . ': ' . implode(', ', $errors));
        }
    }

    /**
     * @param $filter
     * @param array $options
     * @param array $pagination
     * @return Base[]
     */
    public static function select($filter, $options = [], &$pagination = []) {
        $order = isset($options['order']) ? $options['order'] : [];
        $limit = isset($pagination['limit']) ? $pagination['limit'] : 0;
        $offset = isset($pagination['offset']) ? $pagination['offset'] : 0;

        $query = static::query_select($filter, $order, $limit, $offset);

        $stmt = $query->run();
        $pagination['total'] = $query->count();

        $results = [];
        while ($r = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results []= new static($r, $options);
        }

        return $results;
    }

    /**
     * @param $filter
     * @param array $order
     * @param int $limit
     * @param int $offset
     * @return Query
     */
    protected static function query_select($filter, $order = [], $limit = 0, $offset = 0) {
        $fields = static::get_fields();
        $query = new Query();

        foreach ($fields as $field) {
            $query->sel []= $field->db_field();
        }

        $query->from = ['"'. static::CLASS_KEY.'"'];

        foreach ($order as $k => $v) {
            if(isset($fields[$k])) {
                $query->order_by []= $fields[$k]->db_field() . ' ' . (strtolower($v) === 'desc' ? 'desc' : 'asc');
            }
        }

        return $query;
    }

    /**
     * Delete from the database
     * @return bool
     */
    public function delete() {
        $primary = '';
        $id = 0;
        foreach(static::get_fields() as $field) {
            if ($field instanceof PrimaryKeyField) {
                $primary = $field->db_field();
                $id = $primary->value;
            }
        }
        if (!empty($primary)) {
            $stmt = DB::query('delete from "' . static::CLASS_KEY . '" where ' . $primary . ' = ?', [$id]);
            return $stmt->rowCount() > 0;
        } else {
            return false;
        }
    }

    /**
     * @param $values
     * @param array $options
     * @return bool
     * @throws InvalidField
     */
    public function update($values, $options = []) {
        $id = 0;
        $primary = '';
        foreach($this->_fields as $key => $value) {
            if ($this->_fields[$key] instanceof PrimaryKeyField) {
                $primary = $this->_fields[$key]->db_field();
                $id = $this->_fields[$key]->value;
            } else if (!(isset($values[$key]) && $this->_fields[$key]->set_value($values[$key]))) {
                throw new InvalidField("The $key field is invalid.");
            }
        }

        if (!empty($primary)) {
            $query = 'update "' . static::CLASS_KEY . '" set ';
            $q_fields = [];
            $q_values = [];
            foreach($this->_fields as $key => $field) {
                if (!$this->_fields[$key] instanceof PrimaryKeyField) {
                    $q_fields []= $field->db_field() . ' = ?';
                    $q_values []= $field->value;
                }
            }
            $query .= implode(', ', $q_fields) . ' where ' . $primary . ' = ?';

            // add the id for the last "?" param
            $q_values []= $id;

            $stmt = DB::query($query, $q_values);

            return $stmt->rowCount() > 0;
        } else {
            return false;
        }
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
            throw new InvalidField("Can't get field: $name");
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
                throw new InvalidField("Can't set field: $name");
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

    /**
     * Convert this object to a string
     *
     * @return string
     */
    public function __toString() {
        return json_encode($this);
    }

    /**
     * Convert this object to JSON
     *
     * @return array
     */
    public function jsonSerialize() {
        $json = [];
        foreach ($this->_fields as $k => $v) {
            if ($this->_fields[$k]->visible) {
                $json [$k] = $v->value;
            }
        }

        return $json;
    }

}