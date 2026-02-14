<?php
class QuizController {
    private $quiz;

    public function __construct() {
        $this->quiz = new Quiz();
    }

    public function listQuizzes() {
        header('Content-Type: application/json');
        
        try {
            $status = $_GET['status'] ?? null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            $quizzes = $this->quiz->getAll($status, $limit, $offset);
            
            echo json_encode([
                'success' => true,
                'data' => $quizzes
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getQuiz($id) {
        header('Content-Type: application/json');
        
        try {
            $quiz = $this->quiz->getById($id);
            
            if (!$quiz) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Quiz not found'
                ]);
                return;
            }

            echo json_encode([
                'success' => true,
                'data' => $quiz
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function createQuiz() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['title'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Title is required']);
                return;
            }

            $quiz = $this->quiz->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'created_by' => $_SESSION['user_id'] ?? 1,
                'status' => $data['status'] ?? 'draft',
                'passing_score' => $data['passing_score'] ?? 70,
                'time_limit' => $data['time_limit'] ?? null
            ]);

            echo json_encode([
                'success' => true,
                'data' => $quiz
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function startAttempt($quizId) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        try {
            $userId = $_SESSION['user_id'] ?? 1;
            $attemptId = $this->quiz->createAttempt($quizId, $userId);

            echo json_encode([
                'success' => true,
                'data' => ['attempt_id' => $attemptId]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function recordAnswer($attemptId) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['questionId']) || !isset($data['answer'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'questionId and answer are required']);
                return;
            }

            // Get the correct answer
            $db = new Database();
            $question = $db->fetch('SELECT correct_answer FROM quiz_questions WHERE id = ?', [$data['questionId']]);
            $isCorrect = $question['correct_answer'] === $data['answer'];

            $this->quiz->recordAnswer($attemptId, $data['questionId'], $data['answer'], $isCorrect);

            echo json_encode([
                'success' => true,
                'is_correct' => $isCorrect
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function completeAttempt($attemptId) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        try {
            $attempt = $this->quiz->completeAttempt($attemptId);

            echo json_encode([
                'success' => true,
                'data' => $attempt
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getResults($attemptId) {
        header('Content-Type: application/json');
        
        try {
            $attempt = $this->quiz->getAttempt($attemptId);
            $answers = $this->quiz->getAttemptAnswers($attemptId);

            echo json_encode([
                'success' => true,
                'data' => [
                    'attempt' => $attempt,
                    'answers' => $answers
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
?>
