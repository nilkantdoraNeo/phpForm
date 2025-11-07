<?php
// form_responses.php
require_once 'FormManager.php';

$formManager = new FormManager();

$form_id = $_GET['form_id'] ?? 0;
$form = null;
$responses = [];

if ($form_id) {
    // Use numeric ID lookup
    $form = $formManager->getFormById($form_id);
    $responses = $formManager->getFormResponses($form_id);
}

if (!$form) {
    die('Form not found.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Responses - <?php echo htmlspecialchars($form['title']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-lg);
            margin-bottom: var(--space-2xl);
        }
        
        .stat-card {
            background: white;
            padding: var(--space-xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            text-align: center;
            border: 2px solid transparent;
            transition: all var(--transition-base);
        }
        
        .stat-card:hover {
            border-color: var(--primary-300);
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-800) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-label {
            color: var(--neutral-600);
            font-weight: 600;
            margin-top: var(--space-sm);
        }
        
        .response-enter {
            animation: responseEnter 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        @keyframes responseEnter {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Responses: <?php echo htmlspecialchars($form['title']); ?></h1>
            <p>View and analyze all submitted feedback</p>
            <div style="margin-top: var(--space-lg);">
                <a href="admin.php" class="btn">
                    <span>⬅️</span> Back to Admin
                </a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($responses); ?></div>
                <div class="stat-label">Total Responses</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($form['questions']); ?></div>
                <div class="stat-label">Questions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php 
                    if (!empty($responses)) {
                        echo date('M j', strtotime($responses[0]['submitted_at']));
                    } else {
                        echo '-';
                    }
                    ?>
                </div>
                <div class="stat-label">Latest Response</div>
            </div>
        </div>

        <?php if (empty($responses)): ?>
            <div class="card">
                <div style="text-align: center; padding: 3rem; color: var(--neutral-500);">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                    <p style="font-size: 1.125rem; font-weight: 500;">No responses yet.</p>
                    <p style="margin-top: 0.5rem;">Share your form to start collecting feedback!</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($responses as $index => $response): ?>
                <div class="response-item response-enter" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                    <div class="response-header">
                        <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: var(--space-md);">
                            <div>
                                <strong>📧 Email:</strong> 
                                <span style="color: var(--primary-600); font-weight: 600;"><?php echo htmlspecialchars($response['email']); ?></span>
                            </div>
                            <div style="color: var(--neutral-500); font-size: 0.9375rem;">
                                <strong>🕐 Submitted:</strong> <?php echo date('M j, Y g:i A', strtotime($response['submitted_at'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="response-answers">
                        <?php foreach ($response['answers'] as $answer): ?>
                            <div class="answer-item">
                                <strong><?php echo htmlspecialchars($answer['question_text']); ?></strong>
                                <br>
                                <?php echo nl2br(htmlspecialchars($answer['answer_text'])); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>