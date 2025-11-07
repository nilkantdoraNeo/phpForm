<?php
// FormBuilder.php
require_once 'config.php';

class FormBuilder {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function createForm($title, $description) {
        $conn = $this->db->getConnection();
        // Use project-wide unique code naming
        $unique_code = generateUniqueCode();
        $title = $this->db->escape($title);
        $description = $this->db->escape($description);
        
        $sql = "INSERT INTO forms (title, description, unique_code) VALUES ('$title', '$description', '$unique_code')";
        if ($conn->query($sql)) {
            return $conn->insert_id;
        } else {
            return false;
        }
    }
    
    // If a generator is needed locally, use the global helper from config.php.
    // Kept simple: rely on generateUniqueCode() defined in config.php.
    
    public function addField($form_id, $field_type, $label, $placeholder, $options, $is_required, $sort_order) {
        $conn = $this->db->getConnection();
        $form_id = (int)$form_id;
        $field_type = $this->db->escape($field_type);
        $label = $this->db->escape($label);
        $placeholder = $this->db->escape($placeholder);
        $options_json = $this->db->escape(json_encode($options));
        $is_required = $is_required ? 1 : 0;
        $sort_order = (int)$sort_order;
        
        $sql = "INSERT INTO form_fields (form_id, field_type, label, placeholder, options, is_required, sort_order) 
                VALUES ($form_id, '$field_type', '$label', '$placeholder', '$options_json', $is_required, $sort_order)";
        return $conn->query($sql);
    }
    
    public function getFormByLink($link) {
        $conn = $this->db->getConnection();
        $link = $this->db->escape($link);
        $sql = "SELECT * FROM forms WHERE unique_link = '$link' AND is_active = TRUE";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    public function getFormFields($form_id) {
        $conn = $this->db->getConnection();
        $form_id = (int)$form_id;
        $sql = "SELECT * FROM form_fields WHERE form_id = $form_id ORDER BY sort_order ASC";
        $result = $conn->query($sql);
        $fields = [];
        while ($row = $result->fetch_assoc()) {
            $row['options'] = json_decode($row['options'], true);
            $fields[] = $row;
        }
        return $fields;
    }
    
    public function submitResponse($form_id, $email, $answers) {
        $conn = $this->db->getConnection();
        $form_id = (int)$form_id;
        $email = $this->db->escape($email);
        
        // Check if the email has already submitted this form? 
        // If we want to allow only one submission per email per form, we can check here.
        // But the requirement doesn't specify. Let's allow multiple for now.
        
        $sql = "INSERT INTO form_responses (form_id, email) VALUES ($form_id, '$email')";
        if ($conn->query($sql)) {
            $response_id = $conn->insert_id;
            foreach ($answers as $field_id => $answer) {
                $field_id = (int)$field_id;
                if (is_array($answer)) {
                    $answer = json_encode($answer);
                }
                $answer = $this->db->escape($answer);
                $sql = "INSERT INTO response_answers (response_id, field_id, answer) VALUES ($response_id, $field_id, '$answer')";
                $conn->query($sql);
            }
            return true;
        }
        return false;
    }
    
    // Other methods: updateForm, deleteForm, updateField, deleteField, getFormResponses, etc.
}
?>