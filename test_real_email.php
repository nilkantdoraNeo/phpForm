<?php
// test_real_email.php
require_once 'config.php';

echo "<h1>Testing Real Email Sending</h1>";

$to = 'nilkantdora@gmail.com'; // Your email
$subject = 'Test Real Email from Feedback System';
$body = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Feedback System Test</h1>
        </div>
        <div class="content">
            <h2>Hello!</h2>
            <p>This is a <strong>real test email</strong> from your feedback system.</p>
            <p>If you receive this, your email configuration is working correctly!</p>
            <p><a href="http://localhost/feedback-system/admin.php" style="color: #007bff;">Go to Admin Panel</a></p>
        </div>
    </div>
</body>
</html>
';

echo "<p><strong>Testing email to:</strong> $to</p>";
echo "<p><strong>Subject:</strong> $subject</p>";

if (sendEmail($to, $subject, $body)) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px;'>";
    echo "<h3>✅ Email sent successfully!</h3>";
    echo "<p>Check your email inbox and spam folder.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px;'>";
    echo "<h3>❌ Email failed to send</h3>";
    echo "<p>Check the email_log.txt file for error details.</p>";
    echo "</div>";
}

echo "<h3>Email Log:</h3>";
if (file_exists('email_log.txt')) {
    echo "<pre>" . htmlspecialchars(file_get_contents('email_log.txt')) . "</pre>";
} else {
    echo "<p>No email log found yet.</p>";
}

echo "<br><a href='event_manager.php'>Create Event</a>";
?>