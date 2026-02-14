<?php
class Quiz {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function create($data) {
        $id = $this->db->insert('quizzes', $data);
        return $this->getById($id);
    }

    public function getById($id) {
        $quiz = $this->db->fetch('SELECT * FROM quizzes WHERE id = ?', [$id]);
        
        if ($quiz) {
            $quiz['questions'] = $this->getQuestions($id);
            $quiz['attempt_count'] = $this->getAttemptCount($id);
        }
        
        return $quiz;
    }

    public function getAll($status = null, $limit = null, $offset = 0) {
        $sql = 'SELECT * FROM quizzes';
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
        $this->db->update('quizzes', $data, 'id = ?', [$id]);
        return $this->getById($id);
    }

    public function delete($id) {
        return $this->db->delete('quizzes', 'id = ?', [$id]);
    }

    public function addQuestion($quizId, $question, $type = 'multiple_choice', $options = null, $correctAnswer = null, $points = 1) {
        $data = [
            'quiz_id' => $quizId,
            'question' => $question,
            'type' => $type,
            'options' => $options ? json_encode($options) : null,
            'correct_answer' => $correctAnswer,
            'points' => $points,
            'sort_order' => $this->getNextQuestionOrder($quizId)
        ];
        
        return $this->db->insert('quiz_questions', $data);
    }

    public function getQuestions($quizId) {
        $questions = $this->db->fetchAll(
            'SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY sort_order',
            [$quizId]
        );
        
        foreach ($questions as &$q) {
            if ($q['options']) {
                $q['options'] = json_decode($q['options'], true);
            }
        }
        
        return $questions;
    }

    public function createAttempt($quizId, $userId) {
        $data = [
            'quiz_id' => $quizId,
            'user_id' => $userId,
            'started_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('quiz_attempts', $data);
    }

    public function recordAnswer($attemptId, $questionId, $answer, $isCorrect) {
        return $this->db->insert('quiz_answers', [
            'attempt_id' => $attemptId,
            'question_id' => $questionId,
            'answer' => $answer,
            'is_correct' => $isCorrect ? 1 : 0
        ]);
    }

    public function completeAttempt($attemptId) {
        $attempt = $this->db->fetch('SELECT * FROM quiz_attempts WHERE id = ?', [$attemptId]);
        
        $answers = $this->db->fetchAll(
            'SELECT qa.*, qq.points FROM quiz_answers qa 
             JOIN quiz_questions qq ON qa.question_id = qq.id 
             WHERE qa.attempt_id = ? AND qa.is_correct = 1',
            [$attemptId]
        );
        
        $score = 0;
        foreach ($answers as $answer) {
            $score += $answer['points'];
        }
        
        $quiz = $this->db->fetch('SELECT passing_score FROM quizzes WHERE id = ?', [$attempt['quiz_id']]);
        $passed = $score >= $quiz['passing_score'];
        
        $this->db->update('quiz_attempts', [
            'score' => $score,
            'passed' => $passed ? 1 : 0,
            'completed_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$attemptId]);
        
        return $this->getAttempt($attemptId);
    }

    public function getAttempt($id) {
        return $this->db->fetch('SELECT * FROM quiz_attempts WHERE id = ?', [$id]);
    }

    public function getAttemptAnswers($attemptId) {
        return $this->db->fetchAll(
            'SELECT qa.*, qq.question, qq.correct_answer FROM quiz_answers qa 
             JOIN quiz_questions qq ON qa.question_id = qq.id 
             WHERE qa.attempt_id = ?',
            [$attemptId]
        );
    }

    public function getUserAttempts($quizId, $userId) {
        return $this->db->fetchAll(
            'SELECT * FROM quiz_attempts WHERE quiz_id = ? AND user_id = ? ORDER BY created_at DESC',
            [$quizId, $userId]
        );
    }

    public function getAttemptCount($quizId) {
        return $this->db->count('quiz_attempts', 'quiz_id = ?', [$quizId]);
    }

    private function getNextQuestionOrder($quizId) {
        $result = $this->db->fetch(
            'SELECT MAX(sort_order) as max_order FROM quiz_questions WHERE quiz_id = ?',
            [$quizId]
        );
        return ($result['max_order'] ?? 0) + 1;
    }
}
?>
