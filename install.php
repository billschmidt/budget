<?php
/**
 * Bill Schmidt
 * Date: 8/23/2017
 * Time: 10:21 AM
 */

use BillBudget\Base;
use function BillBudget\data_classes;
use BillBudget\Database\DB;
use BillBudget\Fields\PrimaryKeyField;
use BillBudget\Log\ServerLog;
use Monolog\Logger;

require_once('config.inc.php');
require_once('vendor/autoload.php');

ServerLog::log(ServerLog::CHANNEL_CLI, Logger::INFO, 'Installing');

$data_classes = glob(PROJECT_PATH . 'src/Data/*.php');

/** @var Base $full_name */
foreach(data_classes() as $full_name) {
    $table = $full_name::CLASS_KEY;
    $fields = $full_name::get_fields();

    ServerLog::log(ServerLog::CHANNEL_CLI, Logger::INFO, "Creating table: $table");

    $columns = [];
    $primary = '"id"';
    foreach($fields as $f) {
        if ($f instanceof PrimaryKeyField) {
            $primary = '"'.$f->name.'"';
        }
        $columns []= $f->install_field();
    }
    $query = "create table \"$table\" (" . implode(', ', $columns) . ", primary key ($primary))";
    DB::query("drop table if exists \"$table\" cascade");
    DB::query($query);
}

ServerLog::log(ServerLog::CHANNEL_CLI, Logger::INFO, 'Installation Complete');
