<?php
class DashboardController {
    private $user;
    private $survey;
    private $quiz;

    public function __construct() {
        $this->user = new User();
        $this->survey = new Survey();
        $this->quiz = new Quiz();
    }

    public function index() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->user->getById($userId);

        // Get statistics
        $activeSurveys = $this->survey->getAll('active');
        $activeQuizzes = $this->quiz->getAll('active');
        $totalUsers = $this->getTotalUsers();
        $totalSurveyResponses = $this->getTotalResponses();

        $data = [
            'user' => $user,
            'stats' => [
                'active_surveys' => count($activeSurveys),
                'active_quizzes' => count($activeQuizzes),
                'total_users' => $totalUsers,
                'total_responses' => $totalSurveyResponses
            ],
            'recent_surveys' => array_slice($activeSurveys, 0, 5),
            'recent_quizzes' => array_slice($activeQuizzes, 0, 5)
        ];

        return $this->render('dashboard', $data);
    }

    public function getStats() {
        header('Content-Type: application/json');
        
        $activeSurveys = $this->survey->getAll('active');
        $activeQuizzes = $this->quiz->getAll('active');
        
        echo json_encode([
            'success' => true,
            'data' => [
                'active_surveys' => count($activeSurveys),
                'active_quizzes' => count($activeQuizzes),
                'total_users' => $this->getTotalUsers(),
                'total_responses' => $this->getTotalResponses()
            ]
        ]);
    }

    private function getTotalUsers() {
        $db = new Database();
        return $db->count('users');
    }

    private function getTotalResponses() {
        $db = new Database();
        return $db->count('survey_responses');
    }

    private function render($view, $data = []) {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.php';
    }
}
?>
