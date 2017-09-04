<?php
/**
 * Bill Schmidt
 * Date: 8/24/2017
 * Time: 11:21 AM
 */

namespace BillBudget\Controller;


use BillBudget\Base;
use function BillBudget\data_classes;
use function BillBudget\is_assoc;
use BillBudget\Log\ServerLog;
use Monolog\Logger;

class ApiController {

    public $path = [];
    public $method;
    public $request;

    public $status_map = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        429 => 'Too Many Requests',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    ];

    public function __construct() {
        $this->path = $this->parse_path(trim($_SERVER['REQUEST_URI'], '/'));
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        // normalize the files array
        if (!empty($_FILES)) {
            foreach ($_FILES as $k => $v) {
                foreach ($v as $k2 => $v2) {
                    if (is_array($v2)) {
                        // posted name as an array (image_id[file], for example) - file array is backwards, fix it
                        foreach ($v2 as $k3 => $v3) {
                            $request[$k][$k3][$k2] = $v3;
                        }
                    } else {
                        // posted as a standard field name
                        $request[$k][$k2] = $v2;
                    }
                }
            }
        }
    }

    public function parse_path($uri) {
        // first part should be "api" and was already checked
        $path_parts = array_slice(explode('/', $uri), 1);

        $class_key = array_shift($path_parts);
        $next = array_shift($path_parts);

        $class = !empty($class_key) ? data_classes($class_key) : false;

        $id = false;
        $method = false;

        if (is_numeric($next)) {
            $id = $next;
            $next = array_shift($path_parts);
        }

        $t_method = 'api_' . $this->method . '_' . $next;
        if (is_callable([$class, $t_method])) {
            $method = $t_method;
        }

        return [
            'class' => $class,
            'id' => $id,
            'method' => $method,
        ];
    }

    public function process_request() {
        if ($this->path['class'] !== false) {
            try {
                if ($this->method == 'get') {
                    $this->request = $_GET;
                    $this->api_get();
                } else if ($this->method == 'post') {
                    $this->request = $_POST;
                    $this->api_post();
                } else if ($this->method == 'put') {
                    parse_str(file_get_contents('php://input'), $this->request);
                    $this->api_put();
                } else if ($this->method == 'delete') {
                    $this->api_delete();
                } else if ($this->method == 'brew') {
                    // I'm a teapot
                    $this->respond(418);
                } else {
                    $this->respond(400);
                }

            } catch (\Exception $ex) {
                ServerLog::log(ServerLog::CHANNEL_CONTROLLER, Logger::ERROR, 'Request failed.', ServerLog::format_exception($ex));
                $this->respond(500);
            }
        } else {
            $this->respond(400);
        }
    }

    public function respond($status = 200, $payload = '', $message = '', $meta = [], $headers = []) {
        // default to JSON
        if(empty($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        // send headers
        header($_SERVER['SERVER_PROTOCOL'] . ' '. $status . ' '. $this->status_map[$status]);
        foreach ($headers as $head => $val) {
            header("$head: $val");
        }

        $response = ['payload' => $payload];

        if (empty($message)) {
            switch ($status) {
                case 200:
                    $message = 'Your request was processed successfully.';
                    break;
                case 201:
                    $message = 'Your data was saved successfully.';
                    break;
                case 400:
                    $message = 'There was an error processing your request.';
                    break;
                case 401:
                    $message = 'You must be authorized to perform this action.';
                    break;
                case 404:
                    $message = 'The requested resource was not found.';
                    break;
                case 405:
                    $message = 'The requested method is not allowed';
                    if (isset($meta['allow'])) {
                        header($meta['allow']);
                    }
                    break;
                case 418:
                    $message = 'The resulting entity is short and stout.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }
        }

        if (!empty($message)) {
            $response['message'] = $message;
        }
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        if ($headers['Content-Type'] == 'application/json') {
            echo @json_encode($response, JSON_NUMERIC_CHECK | JSON_BIGINT_AS_STRING);
        } else {
            echo $payload;
        }
    }

    public function api_get() {
        /** @var Base $cn */
        $cn = $this->path['class'];

        if (!empty($this->path['id'])) {
            $result = new $cn($this->path['id']);
            $this->respond(200, $result, $cn::READABLE_NAME. ' retrieved.');
        } else {
            // request an array
            $limit = 0;
            $offset = 0;

            if (array_key_exists('limit', $this->request)) {
                $limit = $this->request['limit'];
                unset ($this->request['limit']);
            }

            if (array_key_exists('offset', $this->request)) {
                $offset = $this->request['offset'];
                unset($this->request['offset']);
            }

            $order = [];
            if (isset($this->request['order-by']) && is_assoc($this->request['order-by'])) {
                foreach ($this->request['order-by'] as $k => $v) {
                    $order[$k] = $v;
                }
                unset($this->request['order-by']);
                unset($this->request['dir']);
            }


            $pagination = ['limit' => $limit, 'offset' => $offset, 'total' => 0];
            $result = $cn::select($this->request, ['order' => $order], $pagination);

            $message = $pagination['total'] . ' ' . ($pagination['total'] == 1 ? $cn::READABLE_NAME : $cn::plural_name()) . ' found.';

            $this->respond(200, $result, $message, ['total' => $pagination['total']]);
        }
    }

    public function api_post() {
        
    }

    public function api_put() {
        
    }

    public function api_delete() {

    }
}