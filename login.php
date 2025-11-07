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

// Show registration success message
if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    $message = "Registration successful! Please login.";
    $message_type = 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = $userManager->login($email, $password);
    
    if ($result['success']) {
        // Redirect based on role
        if ($userManager->isAdmin()) {
            header('Location: admin.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $message = $result['message'];
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Feedback System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Login</h1>
            <p>Access your feedback system account</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn">
                    <span>🔐</span> Login
                </button>

                <p style="text-align: center; margin-top: var(--space-lg);">
                    Don't have an account? 
                    <a href="register.php" style="color: var(--primary-600);">Register here</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>