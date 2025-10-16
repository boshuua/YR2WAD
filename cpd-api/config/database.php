<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')  {exit(0); }

class Database {
    private $host = 'localhost';
    private $db_name = 'mydb';
    private $username = 'dev';
    private $password = 'pass';
    private $conn;


    public function getConn() {
        $this->conn = null;
        $dsn = "pgsql:host=" . $this->host . ";dbname=" . $this->db_name;
        try{
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) { exit(json_encode(["message" => "Connection error: " . $e->getMessage()])); }
        return $this->conn;
    }
}