<?php
namespace Memento;

class DatabaseConnection {
    private static $instance;
    private $connection;

    private function __construct() {
        $host = 'localhost';
        $username = 'root';
        $password = '';
        $database = 'kpz_db';

        try {
            $this->connection = new \PDO("mysql:host=$host;dbname=$database", $username, $password);

            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        } catch (\PDOException $e) {
            die('Помилка підключення до бази даних: ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function __clone() {
        trigger_error('Cloning the Singleton instance is not allowed.', E_USER_ERROR);
    }

    public function __wakeup() {
        trigger_error('Unserialization of the Singleton instance is not allowed.', E_USER_ERROR);
    }
}
