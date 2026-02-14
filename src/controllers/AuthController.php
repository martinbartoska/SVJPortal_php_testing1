<?php
class AuthController {
    private $auth;
    private $user;

    public function __construct() {
        $this->auth = Auth::getInstance();
        $this->user = new User();
    }

    public function login() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Email and password required']);
            return;
        }

        $result = $this->auth->login($data['email'], $data['password']);
        
        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(401);
        }

        echo json_encode($result);
    }

    public function register() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->auth->register($data);

        if ($result['success']) {
            http_response_code(201);
        } else {
            http_response_code(400);
        }

        echo json_encode($result);
    }

    public function logout() {
        header('Content-Type: application/json');
        
        $this->auth->logout();
        echo json_encode(['success' => true, 'message' => 'Logout successful']);
    }

    public function requestPasswordReset() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['email'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Email required']);
            return;
        }

        $result = $this->auth->requestPasswordReset($data['email']);
        echo json_encode($result);
    }

    public function resetPassword() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['token']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Token and password required']);
            return;
        }

        $result = $this->auth->resetPassword($data['token'], $data['password']);

        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }

        echo json_encode($result);
    }

    public function changePassword() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        Auth::requireLogin();

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['current_password']) || !isset($data['new_password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Current and new password required']);
            return;
        }

        $result = $this->auth->changePassword($data['current_password'], $data['new_password']);

        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }

        echo json_encode($result);
    }

    public function me() {
        header('Content-Type: application/json');

        Auth::requireLogin();

        $user = $this->auth->getUser();
        echo json_encode(['success' => true, 'data' => $user]);
    }

    public function validateToken() {
        header('Content-Type: application/json');

        $token = $_GET['token'] ?? null;

        if (!$token) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Token required']);
            return;
        }

        $result = $this->auth->validateResetToken($token);
        echo json_encode($result);
    }
}
?>
