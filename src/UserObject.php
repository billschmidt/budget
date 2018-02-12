<?php
/**
 * Bill Schmidt
 * Date: 2/5/2018
 * Time: 2:22 PM
 */

namespace BillBudget;


use BillBudget\Data\User;
use BillBudget\Exceptions\PermissionDenied;
use BillBudget\Fields\ForeignKeyField;

/**
 * Class UserObject
 * @package BillBudget
 * @property int $user_id
 *
 */
class UserObject extends Base {

    /**
     * @return \BillBudget\Field[]
     * @throws \Exception
     */
    public static function get_fields() {
        return array_merge(parent::get_fields(), [
            'user_id' => new ForeignKeyField('user_id', 'User ID', static::CLASS_KEY, ['class_reference' => User::CLASS_KEY]),
        ]);
    }

    /**
     * @throws PermissionDenied
     */
    public function check_user_id() {
        if ($this->user_id != $_SESSION['user_id']) {
            throw new PermissionDenied('Permission Denied');
        }
    }

    /**
     * UserObject constructor.
     * @param $data
     * @param array $options
     * @throws Exceptions\RecordNotFound
     * @throws \Exception
     */
    public function __construct($data, array $options = []) {
        parent::__construct($data, $options);
        $this->check_user_id();
    }

    /**
     * @param $values
     * @param array $options
     * @throws Exceptions\InsertError
     */
    public static function create($values, $options = []) {
        $values['user_id'] = $_SESSION['user_id'];
        parent::create($values, $options);
    }

    /**
     * @param $filter
     * @param array $options
     * @param array $pagination
     * @return Base[]
     * @throws Exceptions\RecordNotFound
     * @throws \Exception
     */
    public static function select($filter, $options = [], &$pagination = []) {
        $filter['user_id'] = $_SESSION['user_id'];
        return parent::select($filter, $options, $pagination);
    }

    /**
     * @return bool
     * @throws PermissionDenied
     */
    public function delete() {
        $this->check_user_id();
        return parent::delete();
    }

    /**
     * @param $values
     * @param array $options
     * @return bool
     * @throws Exceptions\InvalidField
     * @throws PermissionDenied
     */
    public function update($values, $options = []) {
        $this->check_user_id();
        return parent::update($values, $options);
    }


}