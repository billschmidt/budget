<?php
/**
 * Bill Schmidt
 * Date: 8/6/2017
 * Time: 1:29 PM
 */

$yaml = yaml_parse_file(dirname(__FILE__) . '/env.yml');

define('PROJECT_PATH', $yaml['synced_folder'] . DIRECTORY_SEPARATOR);
define('SITE_URL', $yaml['fqdn']);

define('DB_DSN', $yaml['db_dsn']);
define('DB_USER', $yaml['db_name']);
define('DB_PASS', $yaml['db_pass']);

define('DEBUG', true);
