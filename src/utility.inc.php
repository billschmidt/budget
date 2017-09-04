<?php
/**
 * Bill Schmidt
 * Date: 8/21/2017
 * Time: 11:11 AM
 */

namespace BillBudget;

/**
 * Test that an array is associative
 *
 * @param $array
 * @return bool
 */
function is_assoc($array) {
    return (is_array($array) && (count($array) == 0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array))))));
}

/**
 * @param string $key
 * @return array|string|bool
 */
function data_classes($key = '') {
    $class_paths = glob(PROJECT_PATH . 'src/Data/*.php');

    $classes = [];
    foreach($class_paths as $class_path) {
        $classname = substr(basename($class_path), 0, -4);

        /** @var Base $class */
        $class = 'BillBudget\\Data\\' . $classname;

        if (!empty($key) && $key == $class::CLASS_KEY) {
            return $class;
        }
        $classes []= $class;
    }

    if(!empty($key)) {
        return false;
    }

    return $classes;
}
