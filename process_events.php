<?php
// process_events.php
require_once 'EventManager.php';
require_once 'FormManager.php';

// Initialize managers
$eventManager = new EventManager();
$formManager = new FormManager();

// Get events that need processing
$events = $eventManager->getEventsToProcess();

foreach ($events as $event) {
    // Get attendees for this event
    $attendees = $eventManager->getEventAttendees($event['id']);
    
    // Get form details
    $form_link = SITE_URL . '/form.php?code=' . $event['form_code'];
    
    // Prepare email content
    $subject = "Feedback Form for: " . $event['title'];
    $body = "
    <html>
    <body>
        <h2>Feedback Request</h2>
        <p>Hello!</p>
        <p>The event '{$event['title']}' has concluded, and we would greatly appreciate your feedback.</p>
        <p>Please take a moment to fill out our feedback form at: <a href='$form_link'>$form_link</a></p>
        <p>Thank you for your participation!</p>
        <p>Best regards,<br>". SMTP_FROM_NAME ."</p>
    </body>
    </html>";
    
    $success = true;
    
    // Send emails to all attendees
    foreach ($attendees as $email) {
        if (!sendEmail($email, $subject, $body)) {
            $success = false;
            file_put_contents('email_log.txt', 
                "[" . date('Y-m-d H:i:s') . "] Failed to send feedback form for event {$event['id']} to $email\n", 
                FILE_APPEND);
        }
    }
    
    // If all emails were sent successfully, mark the event as completed
    if ($success) {
        $eventManager->markEmailsSent($event['id']);
        file_put_contents('email_log.txt', 
            "[" . date('Y-m-d H:i:s') . "] ✅ Successfully processed event {$event['id']}: {$event['title']}\n", 
            FILE_APPEND);
    }
}

// Output summary
echo "Event processing completed at " . date('Y-m-d H:i:s') . "\n";
?>