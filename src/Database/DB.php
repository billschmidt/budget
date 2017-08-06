<?php
/**
 * Bill Schmidt
 * Date: 8/6/2017
 * Time: 1:16 PM
 */

namespace BillBudget\Database;

/**
 * Class DB
 * @package BillBudget\Database
 */
class DB {
    /** @var \PDO[] */
    private static $instances = [];

    /**
     * Class Constructor - Create a new database connection if one doesn't exist
     * Set to private so no-one can create a new instance via ' = new DB();'
     */
    private function __construct() {
    }

    /**
     * @param $dsn
     * @param $user
     * @param $pass
     * @return \PDO
     */
    public static function getInstance($dsn = DB_DSN, $user = DB_USER, $pass = DB_PASS) {
        $hash = md5($dsn . $user . $pass);

        if (empty(self::$instances[$hash])) {
            self::$instances[$hash] = new \PDO($dsn, $user, $pass);
            self::$instances[$hash]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        return self::$instances[$hash];
    }

    public static function query($query, $params = [], $assoc = false) {
        $objInstance = static::getInstance();

        try {

            $stmt = $objInstance->prepare($query);
            if ($assoc) {
                /**
                 * Unfortunately (and baffling-ly), named parameters are not well supported in PDO
                 * (especially in Postgres), because they aren't considered to be portable enough.
                 *
                 * This rears its ugly head when you try to use two separate functions with the same
                 * named parameter as arguments, like plainto_tsvector(:search) and ts_rank(...,:search)
                 *
                 * See: https://bugs.php.net/bug.php?id=33886 and https://bugs.php.net/bug.php?id=40417
                 */
                $stmt->execute($params);
            } else {
                $stmt->execute(array_values($params));
            }
            return $stmt;
        } catch (\PDOException $e) {
            // log the pdo exception
            ServerLog::log(ServerLog::CHANNEL_PDO, Logger::ERROR, 'Caught Exception', [$e->getTrace(), $query, $params]);

            if (DEBUG) {
                // debug mode - show full error
                throw new \PDOException($e->getMessage() . ' ' . $query . ' params: ' . (count($params) > 0 ? print_r($params, true) : '[None]'));
            } else {
                // not in debug mode - we'll throw the regular exception
                throw $e;
            }
        }
    }
}