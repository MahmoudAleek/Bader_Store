<?php

class Database {

    private $servername = "localhost";
    private $dbname = "bader_store_db";
    private $username = "root";
    private $password = "";
    public $conn;

    // Constructor to establish database connection
    public function __construct() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    // Function to execute SELECT queries with optional parameters
    public function select($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            if (!empty($params)) {
                $types = str_repeat('s', count($params)); // Assuming all parameters are strings
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                return $result->fetch_all(MYSQLI_ASSOC);
            } else {
                return false;
            }
        } else {
            echo "Error preparing statement: " . $this->conn->error;
            return false;
        }
    }

    // Function to execute INSERT, UPDATE, DELETE queries with optional parameters
    public function execute($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $operationType = strtolower(explode(' ', trim($sql))[0]);

            if (!empty($params)) {
                $types = str_repeat('s', count($params)); // Assuming all parameters are strings
                $stmt->bind_param($types, ...$params);
            }


            if(in_array($operationType, ['insert', 'update', 'delete'])){
                if ($stmt->execute()) {
                    if ($operationType === 'insert') {
                        $lastInsertId = $stmt->insert_id;
                        $stmt->close();
                        return $lastInsertId;
                    } else if (in_array($operationType, ['update', 'delete'])) {
                        $affectedRows = $stmt->affected_rows;
                        $stmt->close();
                        return $affectedRows;
                    }
                } else {
                    echo "Error executing statement: " . $stmt->error;
                    return false;
                }
            }
        } else {
            echo "Error preparing statement: " . $this->conn->error;
            return false;
        }
    }

    public function printQuery($query, $params) {
        foreach ($params as $param) {
            $value = is_numeric($param) ? $param : "'" . addslashes($param) . "'";
            $query = preg_replace('/\?/', $value, $query, 1);
        }
    
        return $query;
    }
    

    // Destructor to close database connection
    public function __destruct() {
        $this->conn->close();
    }
}

?>
