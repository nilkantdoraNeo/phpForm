<?php
require_once 'UserManager.php';

$userManager = new UserManager();
$message = '';
$message_type = '';

// Redirect if already logged in
if ($userManager->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if ($password === $confirm_password) {
        $result = $userManager->register($username, $email, $password);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            header('Location: login.php?registered=1');
            exit;
        }
    } else {
        $message = "Passwords do not match";
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Feedback System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Register</h1>
            <p>Create your account to access feedback forms</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           minlength="3" maxlength="50" pattern="[a-zA-Z0-9_-]+"
                           title="Username can contain letters, numbers, underscore and dash">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           minlength="8" title="Minimum 8 characters">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn">
                    <span>✨</span> Register
                </button>

                <p style="text-align: center; margin-top: var(--space-lg);">
                    Already have an account? 
                    <a href="login.php" style="color: var(--primary-600);">Login here</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>