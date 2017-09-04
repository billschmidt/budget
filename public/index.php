<?php
/**
 * Bill Schmidt
 * Date: 8/6/2017
 * Time: 10:02 AM
 */

use BillBudget\Controller\ApiController;

require_once(dirname(dirname(__FILE__)).'/config.inc.php');
require_once(dirname(dirname(__FILE__)).'/src/utility.inc.php');
require_once(dirname(dirname(__FILE__)).'/vendor/autoload.php');

$path_parts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

if (!empty($path_parts) && $path_parts[0] == 'api') {
    $controller = new ApiController();
    $controller->process_request();
    return;
}

// else - load the react app

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Bill Budget</title>

    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <div id="root"></div>

    <script src="js/bundle.js"></script>
</body>

</html>
