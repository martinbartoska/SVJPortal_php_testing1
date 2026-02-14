<?php

class Auth {
    private static $instance = null;
    private $userModel;
    private $sessionTimeout = 3600; // 1 hour

    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new User();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Login user
     */
    public function login($email, $password) {
        $user = $this->userModel->getByEmail($email);

        if (!$user) {
            return ['success' => false, 'error' => 'Invalid email or password'];
        }

        if (!$user['is_active']) {
            return ['success' => false, 'error' => 'Account is inactive'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'Invalid email or password'];
        }

        // Update last login
        $this->userModel->updateLastLogin($user['id']);

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();

        return ['success' => true, 'message' => 'Login successful', 'user' => $user];
    }

    /**
     * Register new user
     */
    public function register($data) {
        // Validate input
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'error' => 'Name, email, and password are required'];
        }

        // Check if email exists
        $existing = $this->userModel->getByEmail($data['email']);
        if ($existing) {
            return ['success' => false, 'error' => 'Email already registered'];
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid email format'];
        }

        // Validate password strength
        if (strlen($data['password']) < 8) {
            return ['success' => false, 'error' => 'Password must be at least 8 characters'];
        }

        try {
            $user = $this->userModel->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'flat_number' => $data['flat_number'] ?? null,
                'phone' => $data['phone'] ?? null,
                'role' => 'resident'
            ]);

            return ['success' => true, 'message' => 'Registration successful', 'user' => $user];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        // Check session timeout
        if (time() - $_SESSION['login_time'] > $this->sessionTimeout) {
            $this->logout();
            return false;
        }

        // Extend session
        $_SESSION['login_time'] = time();
        
        return true;
    }

    /**
     * Get current user
     */
    public function getUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role']
        ];
    }

    /**
     * Get current user ID
     */
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user role
     */
    public function getRole() {
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Check if user has role
     */
    public function hasRole($role) {
        if (is_array($role)) {
            return in_array($this->getRole(), $role);
        }
        return $this->getRole() === $role;
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($permission) {
        $role = $this->getRole();

        $permissions = [
            'admin' => ['create_survey', 'create_quiz', 'manage_users', 'manage_content'],
            'staff' => ['respond_survey', 'take_quiz', 'manage_maintenance'],
            'resident' => ['respond_survey', 'take_quiz', 'request_maintenance']
        ];

        return isset($permissions[$role]) && in_array($permission, $permissions[$role]);
    }

    /**
     * Logout user
     */
    public function logout() {
        session_destroy();
        return true;
    }

    /**
     * Request password reset
     */
    public function requestPasswordReset($email) {
        $user = $this->userModel->getByEmail($email);

        if (!$user) {
            // Security: Don't reveal if email exists
            return ['success' => true, 'message' => 'If email exists, reset link will be sent'];
        }

        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Save token
        $this->userModel->update($user['id'], [
            'reset_token' => $token,
            'reset_token_expires' => $expires
        ]);

        // In production, send email here
        // For now, return token for testing
        return [
            'success' => true,
            'message' => 'Reset link sent to email',
            'token' => $token // Remove in production
        ];
    }

    /**
     * Validate reset token
     */
    public function validateResetToken($token) {
        $user = $this->userModel->getByResetToken($token);

        if (!$user) {
            return ['success' => false, 'error' => 'Invalid reset token'];
        }

        if (strtotime($user['reset_token_expires']) < time()) {
            return ['success' => false, 'error' => 'Reset token has expired'];
        }

        return ['success' => true, 'user' => $user];
    }

    /**
     * Reset password with token
     */
    public function resetPassword($token, $newPassword) {
        $validation = $this->validateResetToken($token);

        if (!$validation['success']) {
            return $validation;
        }

        $user = $validation['user'];

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'error' => 'Password must be at least 8 characters'];
        }

        // Update password and clear token
        $this->userModel->update($user['id'], [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_token_expires' => null
        ]);

        return ['success' => true, 'message' => 'Password reset successful'];
    }

    /**
     * Change password for authenticated user
     */
    public function changePassword($currentPassword, $newPassword) {
        if (!$this->isLoggedIn()) {
            return ['success' => false, 'error' => 'User not authenticated'];
        }

        $userId = $this->getUserId();
        $user = $this->userModel->getById($userId);

        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'error' => 'Current password is incorrect'];
        }

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'error' => 'Password must be at least 8 characters'];
        }

        $this->userModel->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);

        return ['success' => true, 'message' => 'Password changed successfully'];
    }

    /**
     * Require login
     */
    public static function requireLogin() {
        if (!self::getInstance()->isLoggedIn()) {
            header('Location: /login.php');
            exit();
        }
    }

    /**
     * Require role
     */
    public static function requireRole($roles) {
        self::requireLogin();

        if (!self::getInstance()->hasRole($roles)) {
            header('HTTP/1.0 403 Forbidden');
            exit('Access denied');
        }
    }

    /**
     * Prevent if logged in
     */
    public static function preventIfLoggedIn() {
        if (self::getInstance()->isLoggedIn()) {
            header('Location: /index.php');
            exit();
        }
    }
}
?>
