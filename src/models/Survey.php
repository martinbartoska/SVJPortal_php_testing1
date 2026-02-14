<?php
class Survey {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function create($data) {
        $id = $this->db->insert('surveys', $data);
        return $this->getById($id);
    }

    public function getById($id) {
        $survey = $this->db->fetch('SELECT * FROM surveys WHERE id = ?', [$id]);
        
        if ($survey) {
            $survey['questions'] = $this->getQuestions($id);
            $survey['response_count'] = $this->getResponseCount($id);
        }
        
        return $survey;
    }

    public function getAll($status = null, $limit = null, $offset = 0) {
        $sql = 'SELECT * FROM surveys';
        $params = [];
        
        if ($status) {
            $sql .= ' WHERE status = ?';
            $params[] = $status;
        }
        
        $sql .= ' ORDER BY created_at DESC';
        
        if ($limit) {
            $sql .= ' LIMIT ? OFFSET ?';
            $params[] = $limit;
            $params[] = $offset;
        }
        
        return $this->db->fetchAll($sql, $params);
    }

    public function update($id, $data) {
        $this->db->update('surveys', $data, 'id = ?', [$id]);
        return $this->getById($id);
    }

    public function delete($id) {
        return $this->db->delete('surveys', 'id = ?', [$id]);
    }

    public function addQuestion($surveyId, $question, $type = 'text', $options = null) {
        $data = [
            'survey_id' => $surveyId,
            'question' => $question,
            'type' => $type,
            'options' => $options ? json_encode($options) : null,
            'sort_order' => $this->getNextQuestionOrder($surveyId)
        ];
        
        return $this->db->insert('survey_questions', $data);
    }

    public function getQuestions($surveyId) {
        $questions = $this->db->fetchAll(
            'SELECT * FROM survey_questions WHERE survey_id = ? ORDER BY sort_order',
            [$surveyId]
        );
        
        foreach ($questions as &$q) {
            if ($q['options']) {
                $q['options'] = json_decode($q['options'], true);
            }
        }
        
        return $questions;
    }

    public function submitResponse($surveyId, $userId, $responses) {
        foreach ($responses as $questionId => $answer) {
            $this->db->insert('survey_responses', [
                'survey_id' => $surveyId,
                'user_id' => $userId,
                'question_id' => $questionId,
                'answer' => is_array($answer) ? json_encode($answer) : $answer
            ]);
        }
        return true;
    }

    public function getResponses($surveyId, $questionId = null) {
        $sql = 'SELECT * FROM survey_responses WHERE survey_id = ?';
        $params = [$surveyId];
        
        if ($questionId) {
            $sql .= ' AND question_id = ?';
            $params[] = $questionId;
        }
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getResponseCount($surveyId) {
        return $this->db->count('survey_responses', 'survey_id = ?', [$surveyId]);
    }

    public function hasUserResponded($surveyId, $userId) {
        $count = $this->db->count(
            'survey_responses',
            'survey_id = ? AND user_id = ?',
            [$surveyId, $userId]
        );
        return $count > 0;
    }

    private function getNextQuestionOrder($surveyId) {
        $result = $this->db->fetch(
            'SELECT MAX(sort_order) as max_order FROM survey_questions WHERE survey_id = ?',
            [$surveyId]
        );
        return ($result['max_order'] ?? 0) + 1;
    }
}
?>
