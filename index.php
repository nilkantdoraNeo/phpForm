<?php
require_once 'UserManager.php';
require_once 'FormManager.php';

$userManager = new UserManager();
$formManager = new FormManager();

// Redirect to login if not logged in
if (!$userManager->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = $userManager->getCurrentUser();
$assignedForms = $formManager->getFormsForUser($user['email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Forms - Feedback System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1>📝 My Forms</h1>
                <div>
                    <span style="margin-right: 1rem;">
                        Welcome, <?php echo htmlspecialchars($user['username']); ?>!
                    </span>
                    <a href="logout.php" class="btn btn-danger">
                        <span>🚪</span> Logout
                    </a>
                </div>
            </div>
            <p>View and submit your assigned feedback forms</p>
        </div>

        <div class="card">
            <h2>📋 Your Forms</h2>
            
            <?php if (empty($assignedForms)): ?>
                <div style="text-align: center; padding: 3rem; color: var(--neutral-500);">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📝</div>
                    <p style="font-size: 1.125rem; font-weight: 500;">No forms assigned yet.</p>
                    <p style="margin-top: 0.5rem;">Forms will appear here when they are assigned to you.</p>
                </div>
            <?php else: ?>
                <div class="form-list">
                    <?php foreach ($assignedForms as $form): ?>
                        <div class="form-card">
                            <h3><?php echo htmlspecialchars($form['title']); ?></h3>
                            <p><?php echo htmlspecialchars($form['description']); ?></p>
                            
                            <?php if ($form['submitted']): ?>
                                <p style="color: var(--success-600); font-weight: 600;">
                                    ✅ Submitted
                                </p>
                            <?php else: ?>
                                <a href="form.php?code=<?php echo $form['unique_code']; ?>" class="btn">
                                    <span>📝</span> Fill Form
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>