<?php
    class Database {
        private $host = "movie_db";
        private $db_name = "movieDb";
        private $username = "root";
        private $password = "1234";
        private static $instance = null;
        private $conn;
    
        // Private constructor to prevent multiple instances
        private function __construct() {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        }
    
        // Get the single instance of the database connection
        public static function getInstance() {
            if (!self::$instance) {
                self::$instance = new Database();
            }
    
            return self::$instance;
        }
    
        // Get the connection
        public function getConnection() {
            return $this->conn;
        }
    }

?>
