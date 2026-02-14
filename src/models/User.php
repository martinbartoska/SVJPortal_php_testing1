<?php
class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function create($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $id = $this->db->insert('users', $data);
        return $this->getById($id);
    }

    public function getById($id) {
        return $this->db->fetch('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function getByEmail($email) {
        return $this->db->fetch('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function getAll($limit = null, $offset = 0) {
        $sql = 'SELECT * FROM users ORDER BY created_at DESC';
        
        if ($limit) {
            $sql .= ' LIMIT ? OFFSET ?';
            return $this->db->fetchAll($sql, [$limit, $offset]);
        }
        
        return $this->db->fetchAll($sql);
    }

    public function update($id, $data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $this->db->update('users', $data, 'id = ?', [$id]);
        return $this->getById($id);
    }

    public function authenticate($email, $password) {
        $user = $this->getByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return null;
    }

    public function delete($id) {
        return $this->db->delete('users', 'id = ?', [$id]);
    }

    public function getByRole($role) {
        return $this->db->fetchAll('SELECT * FROM users WHERE role = ?', [$role]);
    }

    public function updateLastLogin($id) {
        return $this->db->update('users', ['updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
    }
}
?>
