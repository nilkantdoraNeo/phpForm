<?php
require_once 'config.php';

// Test both email sending methods
$to = SMTP_USERNAME; // Send to the same email configured in SMTP
$subject = "Test Email from Feedback System";
$body = "
<html>
<body>
    <h2>Test Email</h2>
    <p>This is a test email to verify the email configuration.</p>
    <p>Sent at: " . date('Y-m-d H:i:s') . "</p>
    <p>If you receive this, the email system is working correctly.</p>
</body>
</html>";

echo "Attempting to send test email...\n";

// Try sending with both methods
$result = sendEmail($to, $subject, $body);
if ($result) {
    echo "✅ Test email sent successfully!\n";
    echo "Please check your inbox at: $to\n";
} else {
    echo "❌ Failed to send test email\n";
    echo "Please check email_log.txt for details\n";
}
?>