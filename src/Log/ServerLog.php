<?php
/**
 * Bill Schmidt
 * Date: 8/6/2017
 * Time: 1:26 PM
 */

namespace BillBudget\Log;

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;

class ServerLog {
    const CHANNEL_CLI = 'cli';
    const CHANNEL_CLI_HEADER = 'cli_header';
    const CHANNEL_CLI_LINE_BREAK = 'cli_line_break';
    const CHANNEL_CONTROLLER = 'controller';
    const CHANNEL_DEBUG = 'debug';
    const CHANNEL_EXCEPTIONS = 'exceptions';
    const CHANNEL_PDO = 'pdo';
    const CHANNEL_QUERY = 'query';
    const CHANNEL_WTF = 'wtf';

    private static $instances = [];

    public static function get_instance($channel, callable $callback = null, $options = []) {
        if (empty(self::$instances[$channel])) {
            self::$instances[$channel] = new Logger($channel);

            /** @var Logger $log */
            $log = self::$instances[$channel];

            // add any custom handlers, formatters, or processors
            if ($callback != null) {
                call_user_func_array($callback, [$log]);
            }

            $lb_width = isset($options['line-break-width']) ? $options['line-break-width'] : 100;

            switch($channel) {
                case self::CHANNEL_CLI:
                    self::log_cli($log);
                    break;
                case self::CHANNEL_CLI_HEADER:
                    self::log_cli_header($log, $lb_width);
                    break;
                case self::CHANNEL_CLI_LINE_BREAK:
                    self::log_cli_line_break($log, $lb_width);
                    break;
                case self::CHANNEL_CONTROLLER:
                    self::log_default($log);
                    break;
                case self::CHANNEL_EXCEPTIONS:
                    // save warnings and higher for 14 days
                    self::log_rotate($log, Logger::WARNING, 14);
                    $log->pushProcessor(new WebProcessor($_SERVER));
                    break;
                default:
                    self::log_default($log);
            }
        }
        return self::$instances[$channel];
    }

    /**
     * Log a message
     *
     * @param $channel
     * @param $level
     * @param $message
     * @param array $context
     * @param callable|null $callback
     * @param array $options
     * @throws \Exception
     */
    public static function log($channel, $level, $message, $context = [], callable $callback = null, $options = []) {
        $log = self::get_instance($channel, $callback, $options);

        if (!in_array($level, $log->getLevels())) {
            throw new \Exception('Invalid log level: '.$level);
        }

        $log->addRecord($level, $message, $context);
    }

    /**
     * Output a line break
     *
     * @param $level
     * @param int $width
     * @throws \Exception
     */
    public static function line_break($level = Logger::DEBUG, $width = 100) {
        static::log(static::CHANNEL_CLI_LINE_BREAK, $level, '', [], null, ['line-break-width' => $width]);
    }

    /**
     * Just write to a log file, don't do anything special
     *
     * @param Logger $log
     * @param int $level
     */
    private static function log_default(Logger $log, $level = Logger::DEBUG) {
        $handler = new StreamHandler(PROJECT_PATH.'log/'.$log->getName().'.log', $level);
        $log->pushHandler($handler);
    }

    /**
     * Uses ColoredLineFormatter for pretty CLI logs
     *
     * @param Logger $log
     */
    private static function log_cli(Logger $log) {
        static::output_cli($log, "[%datetime%] %level_name%: %message%\n");
    }

    /**
     * Uses ColoredLineFormatter for pretty headers separated by line breaks
     *
     * @param Logger $log
     * @param int $width
     */
    private static function log_cli_header(Logger $log, $width) {
        static::log_cli_line_break($log, $width, "\n");
        static::output_cli($log, "%message%\n");
        static::log_cli_line_break($log, $width);
    }

    /**
     * Spits out a bunch of = signs and a break. Complicated stuff.
     *
     * @param Logger $log
     * @param int $width
     * @param string $prefix
     */
    private static function log_cli_line_break(Logger $log, $width, $prefix = '') {
        $output = '';
        while ($width > 0) {
            $output .= '=';
            $width--;
        }

        static::output_cli($log, $prefix.$output."\n");
    }

    /**
     * Generic output to stdout
     *
     * @param Logger $log
     * @param $output
     */
    private static function output_cli(Logger $log, $output) {
        $handler = new StreamHandler('php://stdout');
        $handler->setFormatter(new ColoredLineFormatter(null, $output, null, true));
        $log->pushHandler($handler);
    }

    /**
     * @param Logger $log
     * @param $level
     * @param int $days
     */
    private static function log_rotate(Logger $log, $level, $days = 0) {
        $handler = new RotatingFileHandler(PROJECT_PATH.'log/'.$log->getName().'.log', $days, $level);
        $log->pushHandler($handler);
    }

    /**
     * Make a nice array for formatting
     *
     * @param \Exception $ex
     * @return array
     */
    public static function format_exception($ex) {
        return [
            'Message' => $ex->getMessage(),
            'File' => $ex->getFile(),
            'Line' => $ex->getLine(),
            'Trace' => $ex->getTrace(),
        ];
    }
}