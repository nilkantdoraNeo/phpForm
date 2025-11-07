<?php
require_once 'config.php';

echo "<h1>Event Checker</h1>";

$db = new Database();
$conn = $db->getConnection();

// Check events
$result = $conn->query("SELECT * FROM events ORDER BY id DESC LIMIT 5");
echo "<h2>Recent Events:</h2>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']} | Title: {$row['title']} | End: {$row['end_time']} | Emails Sent: {$row['emails_sent']}<br>";
    }
} else {
    echo "No events found in database.<br>";
}

// Check event_attendees
$result = $conn->query("SELECT * FROM event_attendees ORDER BY id DESC LIMIT 5");
echo "<h2>Recent Attendees:</h2>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Event ID: {$row['event_id']} | Email: {$row['email']}<br>";
    }
} else {
    echo "No attendees found in database.<br>";
}

echo "<br><a href='event_manager.php'>Create Event</a>";
?>