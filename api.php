<?php
// API Router - Handles all API requests

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load required files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/models/Database.php';
require_once __DIR__ . '/src/models/User.php';
require_once __DIR__ . '/src/models/Survey.php';
require_once __DIR__ . '/src/models/Quiz.php';
require_once __DIR__ . '/src/helpers/Auth.php';
require_once __DIR__ . '/src/controllers/AuthController.php';
require_once __DIR__ . '/src/controllers/DashboardController.php';
require_once __DIR__ . '/src/controllers/SurveyController.php';
require_once __DIR__ . '/src/controllers/QuizController.php';

// Parse URL
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = str_replace('/api', '', $request_uri);
$parts = array_filter(explode('/', $request_uri));

// Route handling
if (empty($parts)) {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit();
}

$controller = null;
$method = null;
$params = [];

// Extract controller and method from URL
if (count($parts) >= 1) {
    $controller = array_shift($parts);
    $method = array_shift($parts) ?: 'index';
    $params = array_values($parts);
}

// Route to appropriate controller
try {
    switch ($controller) {
        case 'auth':
            $ctrl = new AuthController();
            switch ($method) {
                case 'login':
                    $ctrl->login();
                    break;
                case 'register':
                    $ctrl->register();
                    break;
                case 'logout':
                    $ctrl->logout();
                    break;
                case 'forgot-password':
                    $ctrl->requestPasswordReset();
                    break;
                case 'reset-password':
                    $ctrl->resetPassword();
                    break;
                case 'change-password':
                    $ctrl->changePassword();
                    break;
                case 'me':
                    $ctrl->me();
                    break;
                case 'validate-token':
                    $ctrl->validateToken();
                    break;
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Auth endpoint not found']);
            }
            break;

        case 'dashboard':
            $ctrl = new DashboardController();
            Auth::requireLogin();
            if ($method === 'stats') {
                $ctrl->getStats();
            } else {
                $ctrl->index();
            }
            break;

        case 'surveys':
            $ctrl = new SurveyController();
            Auth::requireLogin();
            if ($method === 'list') {
                $ctrl->listSurveys();
            } elseif (is_numeric($method)) {
                $surveyId = $method;
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $ctrl->getSurvey($surveyId);
                } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                    $ctrl->updateSurvey($surveyId);
                } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                    $ctrl->deleteSurvey($surveyId);
                }
            } elseif ($method === 'create') {
                $ctrl->createSurvey();
            } elseif (is_numeric($method) && isset($parts[0]) && $parts[0] === 'responses') {
                $surveyId = $method;
                $ctrl->submitResponse($surveyId);
            } else {
                $ctrl->listSurveys();
            }
            break;

        case 'quizzes':
            $ctrl = new QuizController();
            Auth::requireLogin();
            if ($method === 'list') {
                $ctrl->listQuizzes();
            } elseif (is_numeric($method)) {
                $quizId = $method;
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $ctrl->getQuiz($quizId);
                } elseif ($parts[0] === 'attempt' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $ctrl->startAttempt($quizId);
                }
            } elseif ($method === 'create') {
                $ctrl->createQuiz();
            } elseif ($method === 'attempt' && is_numeric($parts[0])) {
                $attemptId = $parts[0];
                if ($parts[1] === 'complete') {
                    $ctrl->completeAttempt($attemptId);
                } elseif ($parts[1] === 'results') {
                    $ctrl->getResults($attemptId);
                } else {
                    $ctrl->recordAnswer($attemptId);
                }
            } else {
                $ctrl->listQuizzes();
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Controller not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
