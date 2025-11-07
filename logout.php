<?php
require_once 'UserManager.php';

$userManager = new UserManager();
$userManager->logout();

header('Location: login.php');
exit;
?>