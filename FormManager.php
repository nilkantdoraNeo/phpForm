<?php
// FormManager.php
require_once 'config.php';

class FormManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function createForm($title, $description, $questions) {
     $conn = $this->db->getConnection();

     // Generate unique form code (use global helper from config.php)
     $unique_code = generateUniqueCode();
    
    // Insert form
    $title = $this->db->escape($title);
    $description = $this->db->escape($description);
    
    $sql = "INSERT INTO forms (title, description, unique_code) VALUES ('$title', '$description', '$unique_code')";
    
    if ($conn->query($sql)) {
        $form_id = $conn->insert_id;
        
        // Insert questions
        foreach ($questions as $index => $question) {
            $question_text = $this->db->escape($question['text']);
            $question_type = $this->db->escape($question['type']);
            $is_required = isset($question['required']) ? 1 : 0;
            $sort_order = $index;
            
            // FIX: Always ensure options is valid JSON array
            if (isset($question['options']) && is_array($question['options']) && !empty($question['options'])) {
                $options_json = json_encode($question['options']);
            } else {
                $options_json = '[]'; // Empty JSON array instead of null
            }
            
            // Escape the JSON for SQL
            $options_json = $this->db->escape($options_json);
            
            $sql = "INSERT INTO form_questions (form_id, question_text, question_type, is_required, options, sort_order) 
                    VALUES ($form_id, '$question_text', '$question_type', $is_required, '$options_json', $sort_order)";
            
            if (!$conn->query($sql)) {
                // Log error but don't stop the whole process
                error_log("Error inserting question: " . $conn->error);
            }
        }
        
        return $unique_code;
    }
    
    return false;
}
    
    public function getFormByCode($code) {
        $conn = $this->db->getConnection();
        $code = $this->db->escape($code);
        
        $sql = "SELECT * FROM forms WHERE unique_code = '$code' AND is_active = TRUE";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $form = $result->fetch_assoc();
            $form['questions'] = $this->getFormQuestions($form['id']);
            return $form;
        }
        
        return null;
    }

    // Get form by numeric ID
    public function getFormById($id) {
        $conn = $this->db->getConnection();
        $id = (int)$id;

        $sql = "SELECT * FROM forms WHERE id = $id AND is_active = TRUE";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $form = $result->fetch_assoc();
            $form['questions'] = $this->getFormQuestions($form['id']);
            return $form;
        }

        return null;
    }
    
    public function getFormQuestions($form_id) {
        $conn = $this->db->getConnection();
        $form_id = (int)$form_id;
        
        $sql = "SELECT * FROM form_questions WHERE form_id = $form_id ORDER BY sort_order ASC";
        $result = $conn->query($sql);
        
        $questions = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['options']) {
                $row['options'] = json_decode($row['options'], true);
            }
            $questions[] = $row;
        }
        
        return $questions;
    }
    
    public function getAllForms() {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM forms ORDER BY created_at DESC";
        $result = $conn->query($sql);
        
        $forms = [];
        while ($row = $result->fetch_assoc()) {
            $row['response_count'] = $this->getResponseCount($row['id']);
            $forms[] = $row;
        }
        
        return $forms;
    }
    
    public function submitResponse($form_id, $email, $answers) {
        $conn = $this->db->getConnection();
        
        // Escape email early and check if email already submitted for this form
        $email = $this->db->escape($email);
        $check_sql = "SELECT id FROM form_responses WHERE form_id = $form_id AND email = '$email'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            return ['success' => false, 'message' => 'You have already submitted feedback with this email.'];
        }
        
        // Insert response
        $email = $this->db->escape($email);
        $sql = "INSERT INTO form_responses (form_id, email) VALUES ($form_id, '$email')";
        
        if ($conn->query($sql)) {
            $response_id = $conn->insert_id;
            
            // Insert answers
            foreach ($answers as $question_id => $answer) {
                $question_id = (int)$question_id;
                
                if (is_array($answer)) {
                    $answer_text = json_encode($answer);
                } else {
                    $answer_text = $this->db->escape($answer);
                }
                
                $sql = "INSERT INTO response_answers (response_id, question_id, answer_text) 
                        VALUES ($response_id, $question_id, '$answer_text')";
                $conn->query($sql);
            }
            
            return ['success' => true, 'message' => 'Thank you for your feedback!'];
        }
        
        return ['success' => false, 'message' => 'Error submitting feedback.'];
    }
    
    public function getFormResponses($form_id) {
        $conn = $this->db->getConnection();
        $form_id = (int)$form_id;
        
        $sql = "SELECT fr.*, 
                (SELECT COUNT(*) FROM response_answers ra WHERE ra.response_id = fr.id) as answer_count
                FROM form_responses fr 
                WHERE fr.form_id = $form_id 
                ORDER BY fr.submitted_at DESC";
        $result = $conn->query($sql);
        
        $responses = [];
        while ($row = $result->fetch_assoc()) {
            $row['answers'] = $this->getResponseAnswers($row['id']);
            $responses[] = $row;
        }
        
        return $responses;
    }
    
    public function getResponseAnswers($response_id) {
        $conn = $this->db->getConnection();
        $response_id = (int)$response_id;
        
        $sql = "SELECT ra.*, fq.question_text, fq.question_type 
                FROM response_answers ra 
                JOIN form_questions fq ON ra.question_id = fq.id 
                WHERE ra.response_id = $response_id 
                ORDER BY fq.sort_order ASC";
        $result = $conn->query($sql);
        
        $answers = [];
        while ($row = $result->fetch_assoc()) {
            // Decode JSON answers safely for checkbox and other array types
            $answer_text = $row['answer_text'] ?? '';
            $decoded = json_decode($answer_text, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // If it's an array (e.g., checkbox), join for display
                $row['answer_text'] = implode(', ', $decoded);
            } else {
                // leave as-is (string)
                $row['answer_text'] = $answer_text;
            }
            $answers[] = $row;
        }
        
        return $answers;
    }
    
    public function getResponseCount($form_id) {
        $conn = $this->db->getConnection();
        $form_id = (int)$form_id;
        
        $sql = "SELECT COUNT(*) as count FROM form_responses WHERE form_id = $form_id";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }
    
    public function deleteForm($form_id) {
        $conn = $this->db->getConnection();
        $form_id = (int)$form_id;
        
        $sql = "DELETE FROM forms WHERE id = $form_id";
        return $conn->query($sql);
    }

    public function getFormsForUser($email) {
        $conn = $this->db->getConnection();
        $email = $this->db->escape($email);
        
        // Get forms assigned to the user through events
        $sql = "SELECT DISTINCT f.*, e.title as event_title, e.end_time,
                CASE WHEN fr.id IS NOT NULL THEN 1 ELSE 0 END as submitted
                FROM forms f
                JOIN events e ON f.id = e.form_id
                JOIN event_attendees ea ON e.id = ea.event_id
                LEFT JOIN form_responses fr ON (f.id = fr.form_id AND fr.email = '$email')
                WHERE ea.email = '$email'
                ORDER BY e.end_time DESC";
        
        $result = $conn->query($sql);
        
        $forms = [];
        while ($row = $result->fetch_assoc()) {
            $forms[] = $row;
        }
        
        return $forms;
    }
}
?>