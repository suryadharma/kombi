<?php

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    private static $shared = null;

    public function __construct()
    {
        $this->host = getenv('DB_HOST') ?: 'db'; // Menggunakan 'db' sebagai hostname dalam lingkungan Docker
        $this->db_name = getenv('DB_NAME') ?: 'kbs_db';
        $this->username = getenv('DB_USER') ?: 'kbs_user';
        $this->password = getenv('DB_PASS') ?: 'kbs_pass';
        $this->port = getenv('DB_PORT') ?: '3306';
    }

    public function getConnection()
    {
        if (self::$shared instanceof PDO) {
            $this->conn = self::$shared;
            return $this->conn;
        }

        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;port=%s',
                $this->host,
                $this->db_name,
                $this->port
            );

            self::$shared = new PDO($dsn, $this->username, $this->password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            self::$shared->exec("set names utf8");
            $this->conn = self::$shared;
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            // Untuk debugging, tambahkan informasi tambahan
            echo "<br>Host: " . $this->host;
            echo "<br>DB Name: " . $this->db_name;
            echo "<br>Username: " . $this->username;
            echo "<br>Port: " . $this->port;
        }

        return $this->conn;
    }
}
