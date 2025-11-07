<?php
// event_manager.php
require_once 'EventManager.php';
require_once 'FormManager.php';

$eventManager = new EventManager();
$formManager = new FormManager();
$message = '';
$message_type = '';

// Handle event creation
if (isset($_POST['create_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    // timezone offset in minutes (from browser). We'll convert local times to UTC before saving.
    $tz_offset = isset($_POST['tz_offset']) ? intval($_POST['tz_offset']) : 0; // minutes
    $form_id = (int)$_POST['form_id'];
    
    // Process attendees
    $attendees = [];
    $emails = explode("\n", $_POST['attendees']);
    foreach ($emails as $email) {
        $email = trim($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $attendees[] = $email;
        }
    }
    
    if (!empty($attendees)) {
        // Convert start/end to UTC string if tz_offset provided
        if ($tz_offset !== 0) {
            $convert = function($dtStr) use ($tz_offset) {
                $s = str_replace('T', ' ', $dtStr);
                // Try precise format
                $dt = DateTime::createFromFormat('Y-m-d H:i', $s);
                if (!$dt) {
                    // fallback to generic parsing
                    try {
                        $dt = new DateTime($s);
                    } catch (Exception $e) {
                        return $s; // return original if parsing fails
                    }
                }
                // JS getTimezoneOffset returns minutes to add to local time to get UTC.
                $dt->modify($tz_offset . ' minutes');
                return $dt->format('Y-m-d H:i:s');
            };

            $start_time = $convert($start_time);
            $end_time = $convert($end_time);
        } else {
            // normalize format
            $start_time = str_replace('T', ' ', $start_time);
            $end_time = str_replace('T', ' ', $end_time);
        }

        $event_id = $eventManager->createEvent($title, $description, $start_time, $end_time, $attendees, $form_id);
        if ($event_id) {
            // Show a user-friendly local time in the success message (end_time is stored as UTC)
            $display_end_local = $end_time;
            if ($tz_offset !== 0) {
                $end_ts = strtotime($end_time);
                if ($end_ts !== false) {
                    // local = UTC - tz_offset (tz_offset is minutes to add to local to get UTC)
                    $local_ts = $end_ts - ($tz_offset * 60);
                    $display_end_local = date('Y-m-d H:i', $local_ts);
                }
            }

            $message = "Event scheduled successfully! Feedback forms will be sent automatically at $display_end_local (your local time). Stored (UTC): $end_time";
            $message_type = 'success';
        } else {
            $message = "Error creating event.";
            $message_type = 'error';
        }
    } else {
        $message = "Please add at least one valid email address.";
        $message_type = 'error';
    }
}

// Handle event deletion
if (isset($_GET['delete_event'])) {
    $event_id = (int)$_GET['delete_event'];
    if ($eventManager->deleteEvent($event_id)) {
        $message = "Event deleted successfully!";
        $message_type = 'success';
    } else {
        $message = "Error deleting event.";
        $message_type = 'error';
    }
}

$events = $eventManager->getAllEvents();
$forms = $formManager->getAllForms();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Manager - Feedback System</title>
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
                <a href="event_manager.php" class="admin-nav-link active">
                    <span>📅</span> Events
                </a>
                <a href="process_events.php" class="admin-nav-link" title="Process Pending Events">
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
            <h2>🎯 Schedule New Event</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="title">Event Title</label>
                    <input type="text" id="title" name="title" required placeholder="e.g., Advanced PHP Workshop">
                </div>

                <div class="form-group">
                    <label for="description">Event Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Brief description of the event..."></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg);">
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="datetime-local" id="start_time" name="start_time" required>
                    </div>

                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="datetime-local" id="end_time" name="end_time" required>
                    </div>
                </div>
                <input type="hidden" id="tz_offset" name="tz_offset" value="0">

                <div class="form-group">
                    <label for="form_id">Select Feedback Form</label>
                    <select id="form_id" name="form_id" required>
                        <option value="">Choose a form...</option>
                        <?php foreach ($forms as $form): ?>
                            <option value="<?php echo $form['id']; ?>">
                                <?php echo htmlspecialchars($form['title']); ?> (<?php echo $form['response_count']; ?> responses)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="attendees">Attendee Emails (one per line)</label>
                    <textarea id="attendees" name="attendees" rows="6" required placeholder="student1@example.com&#10;student2@example.com&#10;student3@example.com"></textarea>
                    <small style="color: var(--neutral-500); margin-top: var(--space-xs); display: block;">
                        Enter one email address per line. Forms will be sent to all attendees when the event ends.
                    </small>
                </div>

                <button type="submit" name="create_event" class="btn">
                    <span>📅</span> Schedule Event
                </button>
            </form>
        </div>

        <div class="card">
            <h2>📋 Scheduled Events</h2>
            
            <?php if (empty($events)): ?>
                <div style="text-align: center; padding: 3rem; color: var(--neutral-500);">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📅</div>
                    <p style="font-size: 1.125rem; font-weight: 500;">No events scheduled yet.</p>
                    <p style="margin-top: 0.5rem;">Create your first event to get started!</p>
                </div>
            <?php else: ?>
                <div class="form-list">
                    <?php foreach ($events as $event): ?>
                        <div class="form-card">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p><?php echo htmlspecialchars($event['description']); ?></p>
                            <p><strong>📊 Form:</strong> <?php echo htmlspecialchars($event['form_title']); ?></p>
                            <p><strong>👥 Attendees:</strong> <?php echo $event['attendee_count']; ?></p>
                            <p><strong>🕐 Time:</strong>
                                <span class="event-time" data-start="<?php echo htmlspecialchars($event['start_time']); ?>" data-end="<?php echo htmlspecialchars($event['end_time']); ?>">
                                    <?php echo htmlspecialchars($event['start_time']); ?> - <?php echo htmlspecialchars($event['end_time']); ?>
                                </span>
                                <br>
                                <small style="color: #666;">Stored (UTC): <code><?php echo htmlspecialchars($event['start_time']); ?> - <?php echo htmlspecialchars($event['end_time']); ?></code></small>
                            </p>
                            <p><strong>📧 Status:</strong> 
                                <span style="color: <?php echo $event['emails_sent'] ? 'var(--success-600)' : 'var(--warning-600)'; ?>; font-weight: 600;">
                                    <?php echo $event['emails_sent'] ? 'Forms Sent' : 'Scheduled'; ?>
                                </span>
                            </p>
                            
                            <div class="form-actions">
                                <a href="event_manager.php?delete_event=<?php echo $event['id']; ?>" class="btn btn-danger" 
                                   onclick="return confirm('⚠️ Are you sure you want to delete this event? This action cannot be undone.')">
                                    <span>🗑️</span> Delete
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Helper to format a Date object to 'YYYY-MM-DDTHH:MM' in local time for <input type="datetime-local">
        function formatForDateTimeLocal(d) {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const hours = String(d.getHours()).padStart(2, '0');
            const minutes = String(d.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        // Set min datetime to current local time
        const now = new Date();
        const localDateTime = formatForDateTimeLocal(now);
        document.getElementById('start_time').min = localDateTime;
        document.getElementById('end_time').min = localDateTime;
        // fill tz offset (in minutes) so server can convert local times to UTC when saving
        document.getElementById('tz_offset').value = new Date().getTimezoneOffset();

        // Auto-set end time to 1 hour after start time
        document.getElementById('start_time').addEventListener('change', function() {
            const startTime = new Date(this.value);
            const endTime = new Date(startTime.getTime() + 60 * 60 * 1000); // +1 hour
            document.getElementById('end_time').value = formatForDateTimeLocal(endTime);
        });
        
        // Convert UTC stored event times to visitor local time for display
        function toLocalStringFromUTC(dtStr) {
            if (!dtStr) return '';
            let iso = dtStr.trim().replace(' ', 'T');
            if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(iso)) iso += ':00';
            // If string lacks timezone, treat as UTC by appending Z
            if (!iso.endsWith('Z')) iso += 'Z';
            const d = new Date(iso);
            if (isNaN(d)) return dtStr;
            return d.toLocaleString(undefined, {
                month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit'
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.event-time').forEach(function(el) {
                const start = el.dataset.start;
                const end = el.dataset.end;
                const startLocal = toLocalStringFromUTC(start);
                const endLocal = toLocalStringFromUTC(end);
                el.textContent = startLocal + ' - ' + endLocal;
            });
        });
    </script>
</body>
</html>