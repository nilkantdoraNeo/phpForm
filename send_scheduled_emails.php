<?php
// send_scheduled_emails.php
require_once 'EventManager.php';
require_once 'FormManager.php';

class EmailScheduler {
    private $eventManager;
    private $formManager;
    
    public function __construct() {
        $this->eventManager = new EventManager();
        $this->formManager = new FormManager();
    }
    
    public function processScheduledEvents() {
        $events = $this->eventManager->getEventsToProcess();
        $processed = 0;
        
        foreach ($events as $event) {
            $attendees = $this->eventManager->getEventAttendees($event['id']);
            $form = $this->formManager->getFormById($event['form_id']);
            
            if ($form && !empty($attendees)) {
                $successCount = 0;
                
                foreach ($attendees as $email) {
                    if ($this->sendFeedbackEmail($email, $form, $event)) {
                        $successCount++;
                    }
                }
                
                // Mark emails as sent if at least one was successful
                if ($successCount > 0) {
                    $this->eventManager->markEmailsSent($event['id']);
                    $processed++;
                    
                    error_log("Sent $successCount feedback emails for event: " . $event['title']);
                }
            }
        }
        
        return $processed;
    }
    
    private function sendFeedbackEmail($to, $form, $event) {
        $form_url = SITE_URL . '/form.php?code=' . $form['unique_code'];
        
        $subject = "Feedback Request: {$event['title']}";
        
        $body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📝 Feedback Request</h1>
                    <h2>{$event['title']}</h2>
                </div>
                <div class='content'>
                    <p>Hello,</p>
                    <p>Thank you for attending <strong>{$event['title']}</strong>!</p>
                    <p>We would greatly appreciate your feedback to help us improve future sessions.</p>
                    
                    <p><strong>Event Details:</strong></p>
                    <ul>
                        <li><strong>Title:</strong> {$event['title']}</li>
                        <li><strong>Description:</strong> {$event['description']}</li>
                        <li><strong>Time:</strong> " . date('F j, Y g:i A', strtotime($event['start_time'])) . "</li>
                    </ul>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$form_url' class='button' style='color: white;'>Submit Your Feedback</a>
                    </div>
                    
                    <p>This feedback form should take about 2-3 minutes to complete.</p>
                    
                    <p>Thank you for your participation!</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return sendEmail($to, $subject, $body);
    }
}

// Run the scheduler
$scheduler = new EmailScheduler();
$processed = $scheduler->processScheduledEvents();

if (php_sapi_name() === 'cli') {
    echo "Processed $processed events.\n";
} else {
    header('Content-Type: application/json');
    echo json_encode(['processed' => $processed]);
}
?>