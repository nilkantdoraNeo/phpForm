<?php
// form.php
require_once 'FormManager.php';

$formManager = new FormManager();
$message = '';
$message_type = '';

// Get form code from URL
$form_code = $_GET['code'] ?? '';

if (!$form_code) {
    die('Form not found. Please check the URL.');
}

$form = $formManager->getFormByCode($form_code);

if (!$form) {
    die('Form not found or inactive.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $answers = $_POST['answers'] ?? [];
    
    if (empty($email)) {
        $message = 'Email is required.';
        $message_type = 'error';
    } else {
        $result = $formManager->submitResponse($form['id'], $email, $answers);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            // Clear form
            $_POST = [];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($form['title']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo htmlspecialchars($form['title']); ?></h1>
            <p><?php echo htmlspecialchars($form['description']); ?></p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($message_type !== 'success'): ?>
            <div class="card">
                <form method="POST" action="">
                    <div class="form-group required">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo $_POST['email'] ?? ''; ?>">
                    </div>

                    <?php foreach ($form['questions'] as $question): ?>
                        <div class="form-group <?php echo $question['is_required'] ? 'required' : ''; ?>">
                            <label><?php echo htmlspecialchars($question['question_text']); ?></label>
                            
                            <?php if ($question['question_type'] === 'text'): ?>
                                <input type="text" name="answers[<?php echo $question['id']; ?>]" 
                                       <?php echo $question['is_required'] ? 'required' : ''; ?>>
                            
                            <?php elseif ($question['question_type'] === 'textarea'): ?>
                                <textarea name="answers[<?php echo $question['id']; ?>]" rows="4"
                                          <?php echo $question['is_required'] ? 'required' : ''; ?>></textarea>
                            
                            <?php elseif ($question['question_type'] === 'email'): ?>
                                <input type="email" name="answers[<?php echo $question['id']; ?>]"
                                       <?php echo $question['is_required'] ? 'required' : ''; ?>>
                            
                            <?php elseif ($question['question_type'] === 'number'): ?>
                                <input type="number" name="answers[<?php echo $question['id']; ?>]"
                                       <?php echo $question['is_required'] ? 'required' : ''; ?>>
                            
                            <?php elseif ($question['question_type'] === 'radio'): ?>
                                <div class="radio-group">
                                    <?php foreach ($question['options'] as $option): ?>
                                        <label>
                                            <input type="radio" name="answers[<?php echo $question['id']; ?>]" 
                                                   value="<?php echo htmlspecialchars($option); ?>"
                                                   <?php echo $question['is_required'] ? 'required' : ''; ?>>
                                            <?php echo htmlspecialchars($option); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            
                            <?php elseif ($question['question_type'] === 'checkbox'): ?>
                                <div class="checkbox-group">
                                    <?php foreach ($question['options'] as $option): ?>
                                        <label>
                                            <input type="checkbox" name="answers[<?php echo $question['id']; ?>][]" 
                                                   value="<?php echo htmlspecialchars($option); ?>">
                                            <?php echo htmlspecialchars($option); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            
                            <?php elseif ($question['question_type'] === 'dropdown'): ?>
                                <select name="answers[<?php echo $question['id']; ?>]"
                                        <?php echo $question['is_required'] ? 'required' : ''; ?>>
                                    <option value="">Select an option</option>
                                    <?php foreach ($question['options'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option); ?>">
                                            <?php echo htmlspecialchars($option); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            
                            <?php elseif ($question['question_type'] === 'rating'): ?>
                                <div class="rating-stars">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?php echo $i; ?>_<?php echo $question['id']; ?>" 
                                               name="answers[<?php echo $question['id']; ?>]" value="<?php echo $i; ?>"
                                               <?php echo $question['is_required'] ? 'required' : ''; ?>>
                                        <label for="star<?php echo $i; ?>_<?php echo $question['id']; ?>">★</label>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" class="btn">Submit Feedback</button>
                </form>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 2rem;">
            <a href="admin.php" class="btn">Create Your Own Form</a>
        </div>
    </div>
</body>
</html>