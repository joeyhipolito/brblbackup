<?php

class Database {

    protected $conn = null;
    private $stmt;


    public function __construct() {
        $c = new Config();
        $this->conn = new PDO($c->dbtype.':host='.$c->dbhost.';dbname='.$c->dbname, $c->dbuser, $c->dbpass);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
//    public function __construct($dsn, $username, $passwd) {
//        $this->conn = new PDO($dsn, $username, $passwd);
//        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//    }

    public function getConnection() {
        return $this->conn;
    }

    public function query($query, $params = array()) {
        $this->conn->beginTransaction();
            $this->stmt = $this->conn->prepare($query);
            $this->stmt->execute($params);
        $this->conn->commit();
    }

    public function num_rows($query, $params = array()) {
        $this->stmt = $this->conn->prepare($query);
        $params = is_array($params) ? $params : array($params);
        $this->stmt->execute($params);
        return $this->stmt->rowCount();
    }

    public function fetchAssoc($query, $params = array()) {
        $this->stmt = $this->conn->prepare($query);
        $params = is_array($params) ? $params : array($params);
        $this->stmt->execute($params);
        return $this->stmt->fetch();
    }

    public function fetch($query, $params = array()) {
        $this->stmt = $this->conn->prepare($query);
        $params = is_array($params) ? $params : array($params);
        $this->stmt->execute($params);
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchAll($query, $params = array()) {
        $this->stmt = $this->conn->prepare($query);
        $params = is_array($params) ? $params : array($params);
        $this->stmt->execute($params);
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    public function commit() {
        return $this->conn->commit();
    }

    public function __destruct() {
        $this->conn = null;
    }
}
