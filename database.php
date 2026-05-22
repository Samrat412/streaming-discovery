<?php
/**
 * Database Connection Class
 * Singleton pattern for database connections
 */

class Database {
    private static $instance = null;
    private $conn = null;

    private function __construct() {
        try {
            $this->conn = new mysqli(
                DB_HOST,
                DB_USER,
                DB_PASS,
                DB_NAME,
                DB_PORT
            );

            if ($this->conn->connect_error) {
                throw new Exception('Database connection failed: ' . $this->conn->connect_error);
            }

            $this->conn->set_charset(DB_CHARSET);
        } catch (Exception $e) {
            die('Database Error: ' . ($e->getMessage()));
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function query($sql) {
        return $this->conn->query($sql);
    }

    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    public function escape($str) {
        return $this->conn->real_escape_string($str);
    }

    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO $table ($columns) VALUES ($values)";
        
        $stmt = $this->prepare($sql);
        $types = str_repeat('s', count($data));
        $stmt->bind_param($types, ...array_values($data));
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function update($table, $data, $where) {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
        $sql = "UPDATE $table SET $set WHERE $where";
        
        $stmt = $this->prepare($sql);
        $types = str_repeat('s', count($data));
        $stmt->bind_param($types, ...array_values($data));
        
        return $stmt->execute();
    }

    public function delete($table, $where) {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->conn->query($sql);
    }

    public function getAll($table, $where = '1=1', $limit = null, $orderBy = null) {
        $sql = "SELECT * FROM $table WHERE $where";
        if ($orderBy) $sql .= " ORDER BY $orderBy";
        if ($limit) $sql .= " LIMIT $limit";
        
        $result = $this->query($sql);
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function getOne($table, $where) {
        $sql = "SELECT * FROM $table WHERE $where LIMIT 1";
        $result = $this->query($sql);
        return $result->fetch_assoc();
    }

    public function count($table, $where = '1=1') {
        $sql = "SELECT COUNT(*) as cnt FROM $table WHERE $where";
        $result = $this->query($sql);
        $row = $result->fetch_assoc();
        return $row['cnt'];
    }

    public function beginTransaction() {
        return $this->conn->begin_transaction();
    }

    public function commit() {
        return $this->conn->commit();
    }

    public function rollback() {
        return $this->conn->rollback();
    }

    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Get database instance
$db = Database::getInstance();

?>