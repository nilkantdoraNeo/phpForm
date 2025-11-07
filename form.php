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
    <style>
        .form-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-500) 0%, var(--accent-500) 100%);
            transition: width 0.3s ease;
            z-index: 9999;
        }
        
        .success-confetti {
            text-align: center;
            padding: 3rem;
        }
        
        .success-icon {
            font-size: 5rem;
            animation: successBounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        @keyframes successBounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        
        .question-fade-in {
            animation: questionFadeIn 0.5s ease-in-out;
        }
        
        @keyframes questionFadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="form-progress" id="formProgress"></div>
    <div class="container">
        <div class="header">
            <h1><?php echo htmlspecialchars($form['title']); ?></h1>
            <p><?php echo htmlspecialchars($form['description']); ?></p>
        </div>

        <?php if ($message && $message_type === 'success'): ?>
            <div class="card success-confetti">
                <div class="success-icon">🎉</div>
                <h2 style="color: var(--success-600); margin-bottom: 1rem;">Thank You!</h2>
                <p style="font-size: 1.125rem; color: var(--neutral-700);"><?php echo $message; ?></p>
                <div style="margin-top: 2rem;">
                    <a href="admin.php" class="btn">
                        <span>✨</span> Create Your Own Form
                    </a>
                </div>
            </div>
        <?php elseif ($message && $message_type === 'error'): ?>
            <div class="alert alert-error">
                <strong>⚠️ Error:</strong> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($message_type !== 'success'): ?>
            <div class="card">
                <form method="POST" action="" id="feedbackForm">
                    <div class="form-group required question-fade-in">
                        <label for="email">📧 Email Address</label>
                        <input type="email" id="email" name="email" required 
                               placeholder="your.email@example.com"
                               value="<?php echo $_POST['email'] ?? ''; ?>">
                    </div>

                    <?php foreach ($form['questions'] as $index => $question): ?>
                        <div class="form-group <?php echo $question['is_required'] ? 'required' : ''; ?> question-fade-in" style="animation-delay: <?php echo ($index + 1) * 0.1; ?>s;">
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
                            
                            <?php elseif ($question['question_type'] === 'dropdown' || $question['question_type'] === 'select'): ?>
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

                    <button type="submit" class="btn" id="submitBtn">
                        <span>🚀</span> Submit Feedback
                    </button>
                </form>
            </div>
        <?php endif; ?>
        
        <?php if ($message_type !== 'success'): ?>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="admin.php" class="btn" style="background: linear-gradient(135deg, var(--neutral-600) 0%, var(--neutral-700) 100%);">
                    <span>✨</span> Create Your Own Form
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        const form = document.getElementById('feedbackForm');
        const progressBar = document.getElementById('formProgress');
        
        if (form) {
            const inputs = form.querySelectorAll('input, textarea, select');
            let totalFields = inputs.length;
            
            function updateProgress() {
                let filledFields = 0;
                inputs.forEach(input => {
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        const name = input.name;
                        if (form.querySelector(`[name="${name}"]:checked`)) {
                            filledFields++;
                        }
                    } else if (input.value.trim() !== '') {
                        filledFields++;
                    }
                });
                
                const progress = (filledFields / totalFields) * 100;
                progressBar.style.width = progress + '%';
            }
            
            inputs.forEach(input => {
                input.addEventListener('input', updateProgress);
                input.addEventListener('change', updateProgress);
            });
            
            form.addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.innerHTML = '<span class="loading"></span> Submitting...';
                submitBtn.disabled = true;
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.querySelector('.alert-error');
            if (alert) {
                alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    </script>
</body>
</html>
