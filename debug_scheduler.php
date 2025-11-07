<?php
// debug_scheduler.php - SIMPLE VERSION
require_once 'EventManager.php';
require_once 'FormManager.php';

echo "<h1>🔧 Debug Email Scheduler</h1>";

$eventManager = new EventManager();
$formManager = new FormManager();

// Check for events that should be processed
$eventsToProcess = $eventManager->getEventsToProcess();

echo "<h2>Events Ready for Processing:</h2>";
if (empty($eventsToProcess)) {
    echo "<p style='color: orange;'>No events found that need email sending.</p>";
    echo "<p>Create an event that ended recently to test.</p>";
} else {
    echo "<ul>";
    foreach ($eventsToProcess as $event) {
        echo "<li><strong>{$event['title']}</strong> - Ended at: {$event['end_time']}</li>";
    }
    echo "</ul>";
}

// Test email sending manually
echo "<h2>Manual Test:</h2>";
echo "<form method='POST'>";
echo "<button type='submit' name='test_send'>Test Send Emails Now</button>";
echo "</form>";

if (isset($_POST['test_send'])) {
    echo "<h3>Test Results:</h3>";
    
    require_once 'send_scheduled_emails.php';
    $scheduler = new EmailScheduler();
    $processed = $scheduler->processScheduledEvents();
    
    echo "<p>Processed: <strong>$processed</strong> events</p>";
    
    if ($processed > 0) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
        echo "✅ Emails were processed successfully!";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
        echo "⚠️ No events needed processing. Make sure you have events that ended recently.";
        echo "</div>";
    }
}

// Show recent events
echo "<h2>Recent Events:</h2>";
$allEvents = $eventManager->getAllEvents();
if (empty($allEvents)) {
    echo "<p>No events found.</p>";
} else {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>Title</th><th>Start Time</th><th>End Time</th><th>Status</th><th>Emails Sent</th></tr>";
    foreach ($allEvents as $event) {
        $statusColor = $event['emails_sent'] ? 'green' : 'orange';
        echo "<tr>";
        echo "<td>{$event['title']}</td>";
        echo "<td>{$event['start_time']}</td>";
        echo "<td>{$event['end_time']}</td>";
        echo "<td style='color: $statusColor;'>{$event['status']}</td>";
        echo "<td>" . ($event['emails_sent'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<br><br>";
echo "<a href='event_manager.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Create New Event</a>";
echo " &nbsp; ";
echo "<a href='admin.php' style='padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;'>Back to Admin</a>";

// Check log files
echo "<h2>Log Files:</h2>";

// Check cron.log
if (file_exists('cron.log')) {
    $cronSize = filesize('cron.log');
    echo "<p><strong>cron.log</strong> exists - " . $cronSize . " bytes</p>";
    if ($cronSize > 0) {
        $cronContent = file_get_contents('cron.log');
        echo "<pre>" . htmlspecialchars($cronContent) . "</pre>";
    } else {
        echo "<p>cron.log is empty</p>";
    }
} else {
    echo "<p style='color: red;'>cron.log not found</p>";
}

// Check email_log.txt
if (file_exists('email_log.txt')) {
    $emailSize = filesize('email_log.txt');
    echo "<p><strong>email_log.txt</strong> exists - " . $emailSize . " bytes</p>";
    if ($emailSize > 0) {
        $emailContent = file_get_contents('email_log.txt');
        echo "<pre>" . htmlspecialchars($emailContent) . "</pre>";
    } else {
        echo "<p>email_log.txt is empty</p>";
    }
} else {
    echo "<p style='color: orange;'>email_log.txt not created yet</p>";
}
?>