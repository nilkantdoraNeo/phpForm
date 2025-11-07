<?php
// save_form.php
require_once 'FormManager.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formManager = new FormManager();
    
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $fields = $_POST['fields'] ?? [];
    
    // Convert form data to the structure FormManager expects
    $questions = [];
    
    foreach ($fields as $field) {
        $question = [
            'text' => $field['label'] ?? '',
            'type' => $field['type'] ?? 'text',
            'required' => isset($field['required'])
        ];
        
        // Handle options for field types that need them
        if (in_array($field['type'], ['select', 'radio', 'checkbox']) && !empty($field['options'])) {
            $options = explode("\n", $field['options']);
            $options = array_map('trim', $options);
            $options = array_filter($options); // Remove empty lines
            $question['options'] = $options;
        } else {
            $question['options'] = []; // Always set as array, not null
        }
        
        $questions[] = $question;
    }
    
    // Create the form using FormManager
    $formCode = $formManager->createForm($title, $description, $questions);
    
    if ($formCode) {
        header("Location: admin.php?success=Form created successfully! Form Code: " . $formCode);
    } else {
        header("Location: create_form.php?error=Failed to create form");
    }
    exit;
} else {
    header("Location: create_form.php");
    exit;
}
?>