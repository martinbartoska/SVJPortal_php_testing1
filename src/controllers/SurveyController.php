<?php
class SurveyController {
    private $survey;

    public function __construct() {
        $this->survey = new Survey();
    }

    public function listSurveys() {
        header('Content-Type: application/json');
        
        try {
            $status = $_GET['status'] ?? null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            $surveys = $this->survey->getAll($status, $limit, $offset);
            
            echo json_encode([
                'success' => true,
                'data' => $surveys
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getSurvey($id) {
        header('Content-Type: application/json');
        
        try {
            $survey = $this->survey->getById($id);
            
            if (!$survey) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Survey not found'
                ]);
                return;
            }

            echo json_encode([
                'success' => true,
                'data' => $survey
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function createSurvey() {
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

            $survey = $this->survey->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'created_by' => $_SESSION['user_id'] ?? 1,
                'status' => $data['status'] ?? 'draft'
            ]);

            echo json_encode([
                'success' => true,
                'data' => $survey
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function updateSurvey($id) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $survey = $this->survey->update($id, $data);

            echo json_encode([
                'success' => true,
                'data' => $survey
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function deleteSurvey($id) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        try {
            $this->survey->delete($id);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function submitResponse($surveyId) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = $_SESSION['user_id'] ?? 1;

            // Check if user has already responded
            if ($this->survey->hasUserResponded($surveyId, $userId)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'You have already responded to this survey']);
                return;
            }

            $this->survey->submitResponse($surveyId, $userId, $data['responses']);

            echo json_encode(['success' => true]);
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
