<?php
/**
 * Bill Schmidt
 * Date: 8/23/2017
 * Time: 10:21 AM
 */

namespace BillBudget\Data;

use BillBudget\Base;
use BillBudget\Fields\TextField;

class User extends Base {
    const CLASS_KEY = 'user';
    const READABLE_NAME = 'User';

    /**
     * @return \BillBudget\Field[]
     */
    public static function get_fields() {
        return array_merge(parent::get_fields(), [
            'first_name' => new TextField('first_name', 'First Name', static::CLASS_KEY),
            'last_name' => new TextField('last_name', 'Last Name', static::CLASS_KEY),
            'email' => new TextField('email', 'Email', static::CLASS_KEY),
        ]);
    }

    public static function rest_post_login() {
        
    }
}