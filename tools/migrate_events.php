<?php
// tools/migrate_events.php
// Safe interactive script to convert events stored as local times into UTC by applying an offset.
// Usage: open in browser on localhost, enter offset (minutes) and run. BACKUP your DB before running.

require_once __DIR__ . '/..\/config.php';

// Only allow from localhost
$ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
if ($ip !== '127.0.0.1' && $ip !== '::1' && php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}

$db = new Database();
$conn = $db->getConnection();

if (php_sapi_name() === 'cli') {
    echo "This script should be run from the browser on localhost for safety.\n";
    exit;
}

// Handle POST: perform migration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['offset_minutes']) && is_numeric($_POST['offset_minutes'])) {
    $offset = intval($_POST['offset_minutes']);
    $ids = $_POST['ids'] ?? [];
    if (empty($ids)) {
        $message = 'No events selected.';
    } else {
        $updated = 0;
        foreach ($ids as $id) {
            $id = (int)$id;
            // Apply offset (minutes) to stored start_time and end_time by adding the offset minutes.
            // Note: this assumes the stored times are local and we want to convert them to UTC by adding tz_offset minutes
            $sql = "UPDATE events SET start_time = DATE_ADD(start_time, INTERVAL ? MINUTE), end_time = DATE_ADD(end_time, INTERVAL ? MINUTE) WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('iii', $offset, $offset, $id);
                if ($stmt->execute()) $updated++;
                $stmt->close();
            }
        }
        $message = "Updated $updated events.";
    }
}

// Show events
$res = $conn->query("SELECT id, title, start_time, end_time, emails_sent FROM events ORDER BY id DESC LIMIT 200");
$events = [];
while ($row = $res->fetch_assoc()) $events[] = $row;

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Migrate events to UTC</title>
</head>
<body>
<h1>Migrate events to UTC (safe tool)</h1>
<?php if (!empty($message)): ?>
    <div style="padding:8px;background:#e6ffe6;border:1px solid #b6ffb6;margin-bottom:10px;"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>
<p>Backup your DB before running. This tool will add the offset minutes to selected rows' start_time/end_time.
If your stored times are local and your browser reported a timezone offset of -330 (IST), use -330 as the offset (see notes below).</p>
<form method="post">
    <label>Offset minutes (getTimezoneOffset value from browser): <input type="number" name="offset_minutes" value="0"></label>
    <p>Select events to update:</p>
    <div style="max-height:300px;overflow:auto;border:1px solid #ccc;padding:8px;">
        <?php foreach ($events as $e): ?>
            <label style="display:block;margin-bottom:6px;">
                <input type="checkbox" name="ids[]" value="<?php echo $e['id']; ?>"> 
                #<?php echo $e['id']; ?> - <?php echo htmlspecialchars($e['title']); ?> - stored: <?php echo $e['start_time']; ?> -> <?php echo $e['end_time']; ?> (sent: <?php echo $e['emails_sent']; ?>)
            </label>
        <?php endforeach; ?>
    </div>
    <p><button type="submit">Migrate selected events</button></p>
</form>
<hr>
<p>Notes:</p>
<ul>
<li>JS getTimezoneOffset returns minutes to add to local time to get UTC. E.g., IST (UTC+5:30) returns -330.</li>
<li>If your stored times were local and you want them converted to UTC, use the same offset value your browser reported when you created events, e.g. -330.</li>
</ul>
</body>
</html>
