<?php
// admin.php
require_once 'FormManager.php';

$formManager = new FormManager();
$message = '';
$message_type = '';

// Handle form creation
if (isset($_POST['create_form'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $questions = [];
    
    foreach ($_POST['questions'] as $question) {
        if (!empty($question['text'])) {
            $q = [
                'text' => $question['text'],
                'type' => $question['type'],
                'required' => isset($question['required'])
            ];
            
            if (in_array($question['type'], ['radio', 'checkbox', 'dropdown']) && !empty($question['options'])) {
                $q['options'] = array_filter($question['options']);
            }
            
            $questions[] = $q;
        }
    }
    
    if (!empty($questions)) {
        $form_code = $formManager->createForm($title, $description, $questions);
        if ($form_code) {
            $message = "Form created successfully! Share this link: " . SITE_URL . "/form.php?code=" . $form_code;
            $message_type = 'success';
        } else {
            $message = "Error creating form.";
            $message_type = 'error';
        }
    } else {
        $message = "Please add at least one question.";
        $message_type = 'error';
    }
}

// Handle form deletion
if (isset($_GET['delete_form'])) {
    $form_id = (int)$_GET['delete_form'];
    if ($formManager->deleteForm($form_id)) {
        $message = "Form deleted successfully!";
        $message_type = 'success';
    } else {
        $message = "Error deleting form.";
        $message_type = 'error';
    }
}

$forms = $formManager->getAllForms();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Feedback System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin-nav.css">
    <style>
        .success-animation {
            animation: successPulse 0.6s ease-in-out;
        }
        
        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        
        .copy-success {
            background: linear-gradient(135deg, var(--success-500) 0%, var(--success-600) 100%) !important;
        }
        
        .form-card-enter {
            animation: cardEnter 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        @keyframes cardEnter {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✨ Admin Dashboard</h1>
            <p>Manage your feedback system</p>
            
            <nav class="admin-nav">
                <a href="admin.php" class="admin-nav-link <?php echo !isset($_GET['page']) ? 'active' : ''; ?>">
                    <span>📝</span> Forms
                </a>
                <a href="event_manager.php" class="admin-nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'events' ? 'active' : ''; ?>">
                    <span>📅</span> Events
                </a>
                <a href="process_events.php" class="admin-nav-link" title="Process Pending Events">
                    <span>⚡</span> Process Events
                </a>
                <a href="logout.php" class="admin-nav-link" style="margin-left: auto;">
                    <span>🚪</span> Logout
                </a>
            </nav>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="nav-tabs">
            <button class="nav-tab active" onclick="switchTab('create')">
                <span>📝</span> Create Form
            </button>
            <button class="nav-tab" onclick="switchTab('manage')">
                <span>📊</span> Manage Forms
            </button>
        </div>

        <div id="create" class="tab-content active">
            <div class="card">
                <h2>🎨 Create New Form</h2>
                <form method="POST" action="" id="createFormForm">
                    <div class="form-group">
                        <label for="title">Form Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Form Description</label>
                        <textarea id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="form-builder">
                        <h3>Form Questions</h3>
                        <div id="questions-container">
                            <div class="question-item">
                                <div class="form-group">
                                    <label>Question 1</label>
                                    <input type="text" name="questions[0][text]" placeholder="Enter your question" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Question Type</label>
                                    <select name="questions[0][type]" onchange="toggleOptions(this)">
                                        <option value="text">Text Input</option>
                                        <option value="textarea">Text Area</option>
                                        <option value="email">Email</option>
                                        <option value="number">Number</option>
                                        <option value="radio">Radio Buttons</option>
                                        <option value="checkbox">Checkboxes</option>
                                        <option value="dropdown">Dropdown</option>
                                        <option value="rating">Star Rating</option>
                                    </select>
                                </div>

                                <div class="form-group options-input" style="display: none;">
                                    <label>Options (one per line)</label>
                                    <div class="options-container">
                                        <div class="option-item">
                                            <input type="text" name="questions[0][options][]" placeholder="Option 1">
                                            <button type="button" class="remove-option" onclick="removeOption(this)">×</button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn" onclick="addOption(this)">Add Option</button>
                                </div>

                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="questions[0][required]"> Required question
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn" onclick="addQuestion()">Add Another Question</button>
                    </div>

                    <button type="submit" name="create_form" class="btn" style="margin-top: 2rem;">
                        <span>🚀</span> Create Form
                    </button>
                </form>
            </div>
        </div>

        <div id="manage" class="tab-content">
            <div class="card">
                <h2>📋 Manage Forms</h2>
                
                <?php if (empty($forms)): ?>
                    <div style="text-align: center; padding: 3rem; color: var(--neutral-500);">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">📝</div>
                        <p style="font-size: 1.125rem; font-weight: 500;">No forms created yet.</p>
                        <p style="margin-top: 0.5rem;">Create your first form to get started!</p>
                    </div>
                <?php else: ?>
                    <div class="form-list">
                        <?php foreach ($forms as $index => $form): ?>
                            <div class="form-card form-card-enter" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                <h3><?php echo htmlspecialchars($form['title']); ?></h3>
                                <p><?php echo htmlspecialchars($form['description']); ?></p>
                                <p><strong>📊 Responses:</strong> <span style="color: var(--primary-600); font-weight: 700;"><?php echo $form['response_count']; ?></span></p>
                                <p><strong>📅 Created:</strong> <?php echo date('M j, Y', strtotime($form['created_at'])); ?></p>
                                
                                <div class="copy-link">
                                    <input type="text" value="<?php echo SITE_URL . '/form.php?code=' . $form['unique_code']; ?>" readonly id="link-<?php echo $form['id']; ?>">
                                    <button type="button" class="btn" onclick="copyLink(this, '<?php echo $form['id']; ?>')">
                                        <span id="copy-icon-<?php echo $form['id']; ?>">📋</span>
                                        <span id="copy-text-<?php echo $form['id']; ?>">Copy</span>
                                    </button>
                                </div>
                                
                                <div class="form-actions">
                                    <a href="form_responses.php?form_id=<?php echo $form['id']; ?>" class="btn btn-success">
                                        <span>👁️</span> View Responses
                                    </a>
                                    <a href="admin.php?delete_form=<?php echo $form['id']; ?>" class="btn btn-danger" 
                                       onclick="return confirm('⚠️ Are you sure you want to delete this form? This action cannot be undone.')">
                                        <span>🗑️</span> Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        let questionCount = 1;

        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        function addQuestion() {
            const container = document.getElementById('questions-container');
            const newQuestion = document.createElement('div');
            newQuestion.className = 'question-item';
            newQuestion.innerHTML = `
                <div class="form-group">
                    <label>Question ${questionCount + 1}</label>
                    <input type="text" name="questions[${questionCount}][text]" placeholder="Enter your question" required>
                </div>
                
                <div class="form-group">
                    <label>Question Type</label>
                    <select name="questions[${questionCount}][type]" onchange="toggleOptions(this)">
                        <option value="text">Text Input</option>
                        <option value="textarea">Text Area</option>
                        <option value="email">Email</option>
                        <option value="number">Number</option>
                        <option value="radio">Radio Buttons</option>
                        <option value="checkbox">Checkboxes</option>
                        <option value="dropdown">Dropdown</option>
                        <option value="rating">Star Rating</option>
                    </select>
                </div>

                <div class="form-group options-input" style="display: none;">
                    <label>Options (one per line)</label>
                    <div class="options-container">
                        <div class="option-item">
                            <input type="text" name="questions[${questionCount}][options][]" placeholder="Option 1">
                            <button type="button" class="remove-option" onclick="removeOption(this)">×</button>
                        </div>
                    </div>
                    <button type="button" class="btn" onclick="addOption(this)">Add Option</button>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="questions[${questionCount}][required]"> Required question
                    </label>
                </div>

                <button type="button" class="btn btn-danger" onclick="removeQuestion(this)">Remove Question</button>
            `;
            
            container.appendChild(newQuestion);
            questionCount++;
        }

        function removeQuestion(button) {
            button.closest('.question-item').remove();
            // Update question numbers
            questionCount--;
        }

        function toggleOptions(select) {
            const questionItem = select.closest('.question-item');
            const optionsInput = questionItem.querySelector('.options-input');
            const type = select.value;
            
            if (['radio', 'checkbox', 'dropdown'].includes(type)) {
                optionsInput.style.display = 'block';
            } else {
                optionsInput.style.display = 'none';
            }
        }

        function addOption(button) {
            const optionsContainer = button.previousElementSibling;
            const optionCount = optionsContainer.children.length;
            const newOption = document.createElement('div');
            newOption.className = 'option-item';
            newOption.innerHTML = `
                <input type="text" name="${button.closest('.options-input').querySelector('input').name}" placeholder="Option ${optionCount + 1}">
                <button type="button" class="remove-option" onclick="removeOption(this)">×</button>
            `;
            optionsContainer.appendChild(newOption);
        }

        function removeOption(button) {
            if (button.closest('.options-container').children.length > 1) {
                button.closest('.option-item').remove();
            }
        }

        function copyLink(button, formId) {
            const input = document.getElementById('link-' + formId);
            const icon = document.getElementById('copy-icon-' + formId);
            const text = document.getElementById('copy-text-' + formId);
            
            input.select();
            input.setSelectionRange(0, 99999); // For mobile devices
            
            // Modern clipboard API
            navigator.clipboard.writeText(input.value).then(() => {
                // Success animation
                button.classList.add('copy-success');
                icon.textContent = '✅';
                text.textContent = 'Copied!';
                
                setTimeout(() => {
                    button.classList.remove('copy-success');
                    icon.textContent = '📋';
                    text.textContent = 'Copy';
                }, 2000);
            }).catch(() => {
                // Fallback for older browsers
                document.execCommand('copy');
                button.classList.add('copy-success');
                icon.textContent = '✅';
                text.textContent = 'Copied!';
                
                setTimeout(() => {
                    button.classList.remove('copy-success');
                    icon.textContent = '📋';
                    text.textContent = 'Copy';
                }, 2000);
            });
        }
        
        // Add smooth scroll behavior
        document.addEventListener('DOMContentLoaded', function() {
            // Animate alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.classList.add('success-animation');
            });
            
            // Add stagger animation to form cards
            const formCards = document.querySelectorAll('.form-card');
            formCards.forEach((card, index) => {
                card.style.animationDelay = (index * 0.1) + 's';
            });
        });
    </script>
</body>
</html>