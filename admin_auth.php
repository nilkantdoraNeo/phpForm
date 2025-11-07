<?php
require_once 'UserManager.php';

$userManager = new UserManager();

// Check if user is logged in and is admin
if (!$userManager->isLoggedIn() || !$userManager->isAdmin()) {
    header('Location: login.php');
    exit;
}

// Rest of your existing admin.php code here
?>