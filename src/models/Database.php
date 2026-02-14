<?php
class Database {
    private $conn;

    public function __construct() {
        require_once __DIR__ . '/../../config/database.php';
        $this->conn = $GLOBALS['conn'] ?? $conn;
    }

    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $this->conn->error);
        }

        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new Exception("Query execution failed: " . $stmt->error);
        }

        return $stmt;
    }

    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function insert($table, $data) {
        $keys = array_keys($data);
        $values = array_values($data);
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        
        $sql = "INSERT INTO $table (" . implode(',', $keys) . ") VALUES (" . $placeholders . ")";
        
        $stmt = $this->query($sql, $values);
        return $this->conn->insert_id;
    }

    public function update($table, $data, $where, $whereValues) {
        $sets = [];
        foreach (array_keys($data) as $key) {
            $sets[] = "$key = ?";
        }
        
        $sql = "UPDATE $table SET " . implode(',', $sets) . " WHERE $where";
        
        $params = array_merge(array_values($data), $whereValues);
        return $this->query($sql, $params);
    }

    public function delete($table, $where, $whereValues) {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->query($sql, $whereValues);
    }

    public function count($table, $where = '', $whereValues = []) {
        $sql = "SELECT COUNT(*) as count FROM $table";
        
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }

        $result = $this->fetch($sql, $whereValues);
        return $result['count'] ?? 0;
    }
}
?>
