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

    public function getByResetToken($token) {
        return $this->db->fetch('SELECT * FROM users WHERE reset_token = ?', [$token]);
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
        if (isset($data['password']) && !empty($data['password'])) {
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
        return $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
    }

    public function activate($id) {
        return $this->db->update('users', ['is_active' => 1], 'id = ?', [$id]);
    }

    public function deactivate($id) {
        return $this->db->update('users', ['is_active' => 0], 'id = ?', [$id]);
    }

    public function getActive() {
        return $this->db->fetchAll('SELECT * FROM users WHERE is_active = 1 ORDER BY created_at DESC');
    }

    public function count($where = '', $whereValues = []) {
        return $this->db->count('users', $where, $whereValues);
    }

    public function search($query, $limit = 10) {
        return $this->db->fetchAll(
            'SELECT * FROM users WHERE name LIKE ? OR email LIKE ? OR flat_number LIKE ? LIMIT ?',
            ["%$query%", "%$query%", "%$query%", $limit]
        );
    }
}
?>

