<?php
// config.php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'advanced_feedback_system');
define('SITE_URL', 'http://localhost/feedback-system');

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'nilkantdora@gmail.com');
define('SMTP_PASSWORD', 'wpul rlam ozzr nndf');
define('SMTP_FROM_EMAIL', 'nilkantdora@gmail.com');
define('SMTP_FROM_NAME', 'Feedback System');

class Database {
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function escape($data) {
        return $this->connection->real_escape_string(trim($data));
    }
    
    public function query($sql) {
        return $this->connection->query($sql);
    }
    
    public function insertId() {
        return $this->connection->insert_id;
    }
}

function generateUniqueCode($length = 10) {
    return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
}

// WORKING EMAIL FUNCTION - Uses XAMPP's mail()
function sendEmail($to, $subject, $body, $isHTML = true) {
    $headers = [];
    
    if ($isHTML) {
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
    }
    
    $headers[] = 'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>';
    $headers[] = 'Reply-To: ' . SMTP_FROM_EMAIL;
    $headers[] = 'X-Mailer: PHP/' . phpversion();
    
    $fullHeaders = implode("\r\n", $headers);
    
    // Log the attempt
    $log_entry = "[" . date('Y-m-d H:i:s') . "] ATTEMPTING TO SEND EMAIL:\n";
    $log_entry .= "To: $to\n";
    $log_entry .= "Subject: $subject\n";
    $log_entry .= "Headers: " . $fullHeaders . "\n";
    $log_entry .= "------------------------\n";
    file_put_contents('email_log.txt', $log_entry, FILE_APPEND);
    
    // Try to send email
    if (mail($to, $subject, $body, $fullHeaders)) {
        $success_log = "[" . date('Y-m-d H:i:s') . "] ✅ EMAIL SENT SUCCESSFULLY TO: $to\n";
        file_put_contents('email_log.txt', $success_log, FILE_APPEND);
        return true;
    } else {
        $error_log = "[" . date('Y-m-d H:i:s') . "] ❌ EMAIL FAILED TO: $to\n";
        $error_log .= "Error: " . error_get_last()['message'] . "\n";
        file_put_contents('email_log.txt', $error_log, FILE_APPEND);
        
        // Fallback: Try PHPMailer if available
        return sendEmailWithPHPMailer($to, $subject, $body);
    }
}

// Fallback function using PHPMailer
function sendEmailWithPHPMailer($to, $subject, $body) {
    // Check if PHPMailer is available
    if (!file_exists('vendor/autoload.php')) {
        return false;
    }
    
    require_once 'vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        // Disable SSL verification for testing
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        if ($mail->send()) {
            file_put_contents('email_log.txt', "[" . date('Y-m-d H:i:s') . "] ✅ PHPMailer SUCCESS: Email sent to $to\n", FILE_APPEND);
            return true;
        } else {
            file_put_contents('email_log.txt', "[" . date('Y-m-d H:i:s') . "] ❌ PHPMailer FAILED: " . $mail->ErrorInfo . "\n", FILE_APPEND);
            return false;
        }
    } catch (Exception $e) {
        file_put_contents('email_log.txt', "[" . date('Y-m-d H:i:s') . "] ❌ PHPMailer EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}
?>