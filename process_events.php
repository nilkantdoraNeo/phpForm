<?php
require_once 'UserManager.php';
require_once 'EventManager.php';
require_once 'FormManager.php';

// Check admin access
$userManager = new UserManager();
if (!$userManager->isLoggedIn() || !$userManager->isAdmin()) {
    header('Location: login.php');
    exit;
}

// Initialize managers
$eventManager = new EventManager();
$formManager = new FormManager();

$message = '';
$message_type = '';
$processed_events = [];

// Process events when requested
if (isset($_POST['process_events'])) {
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
        $failed_emails = [];
        
        // Send emails to all attendees
        foreach ($attendees as $email) {
            if (!sendEmail($email, $subject, $body)) {
                $success = false;
                $failed_emails[] = $email;
                file_put_contents('email_log.txt', 
                    "[" . date('Y-m-d H:i:s') . "] Failed to send feedback form for event {$event['id']} to $email\n", 
                    FILE_APPEND);
            }
        }
        
        // Record processing result
        $event_result = [
            'id' => $event['id'],
            'title' => $event['title'],
            'success' => $success,
            'attendees_count' => count($attendees),
            'failed_emails' => $failed_emails
        ];
        
        $processed_events[] = $event_result;
        
        // If all emails were sent successfully, mark the event as completed
        if ($success) {
            $eventManager->markEmailsSent($event['id']);
            file_put_contents('email_log.txt', 
                "[" . date('Y-m-d H:i:s') . "] ✅ Successfully processed event {$event['id']}: {$event['title']}\n", 
                FILE_APPEND);
        }
    }
    
    if (empty($events)) {
        $message = "No events pending for processing.";
        $message_type = 'info';
    } else {
        $message = "Processed " . count($events) . " event(s).";
        $message_type = 'success';
    }
}

// Get pending events for display
$pending_events = $eventManager->getEventsToProcess();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Events - Feedback System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin-nav.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✨ Admin Dashboard</h1>
            <p>Manage your feedback system</p>
            
            <nav class="admin-nav">
                <a href="admin.php" class="admin-nav-link">
                    <span>📝</span> Forms
                </a>
                <a href="event_manager.php" class="admin-nav-link">
                    <span>📅</span> Events
                </a>
                <a href="process_events.php" class="admin-nav-link active" title="Process Pending Events">
                    <span>⚡</span> Process Events
                </a>
                <a href="logout.php" class="admin-nav-link" style="margin-left: auto;">
                    <span>🚪</span> Logout
                </a>
            </nav>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>⚡ Process Events</h2>
            
            <?php if (empty($pending_events)): ?>
                <div style="text-align: center; padding: 3rem; color: var(--neutral-500);">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">✨</div>
                    <p style="font-size: 1.125rem; font-weight: 500;">No events pending for processing</p>
                    <p style="margin-top: 0.5rem;">All events are up to date!</p>
                </div>
            <?php else: ?>
                <div style="margin-bottom: 2rem;">
                    <h3>Pending Events</h3>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($pending_events as $event): ?>
                            <li style="padding: 1rem; border-bottom: 1px solid var(--neutral-200);">
                                <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                <br>
                                <small>End Time: <?php echo $event['end_time']; ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <form method="POST" action="">
                    <button type="submit" name="process_events" class="btn">
                        <span>⚡</span> Process Events Now
                    </button>
                </form>
            <?php endif; ?>

            <?php if (!empty($processed_events)): ?>
                <div style="margin-top: 2rem;">
                    <h3>Processing Results</h3>
                    <?php foreach ($processed_events as $event): ?>
                        <div class="alert alert-<?php echo $event['success'] ? 'success' : 'error'; ?>" style="margin-bottom: 1rem;">
                            <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                            <br>
                            <?php if ($event['success']): ?>
                                ✅ Successfully sent emails to <?php echo $event['attendees_count']; ?> attendee(s)
                            <?php else: ?>
                                ❌ Failed to send emails to: <?php echo implode(', ', $event['failed_emails']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
?>