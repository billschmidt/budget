<?php
/**
 * Bill Schmidt
 * Date: 8/6/2017
 * Time: 1:29 PM
 */

$yaml = yaml_parse('vagrant.yml');

define('PROJECT_PATH', $yaml['synced_folder'] . DIRECTORY_SEPARATOR);
define('SITE_URL', $yaml['fqdn']);