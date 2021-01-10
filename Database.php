<?php
namespace App;

// require our api configuration variables
require_once('config.php');

class Database
{
    protected static $instance;
    protected $pdo;

    public function __construct()
    {
        $options = array(
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        );

        $dsn = 'mysql:host='.PDO_HOST.';dbname='.PDO_DB.';charset='.PDO_CHARSET.';port='.PDO_PORT;
        $this->pdo = new \PDO($dsn, PDO_USER, PDO_PASS, $options);
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    // a helper function to run prepared statements smoothly
    public function run($sql, $args = array())
    {
        if (!$args) {
             return $this->pdo->query($sql);
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }
}
