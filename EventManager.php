<?php
// EventManager.php
require_once 'config.php';

class EventManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function createEvent($title, $description, $start_time, $end_time, $attendees, $form_id) {
        $conn = $this->db->getConnection();
        
        $title = $this->db->escape($title);
        $description = $this->db->escape($description);
        $start_time = $this->db->escape($start_time);
        $end_time = $this->db->escape($end_time);
        $form_id = (int)$form_id;
        
        $sql = "INSERT INTO events (title, description, start_time, end_time, form_id, status) 
                VALUES ('$title', '$description', '$start_time', '$end_time', $form_id, 'scheduled')";
        
        if ($conn->query($sql)) {
            $event_id = $conn->insert_id;
            
            // Add attendees
            foreach ($attendees as $email) {
                $email = $this->db->escape(trim($email));
                if (!empty($email)) {
                    $sql = "INSERT INTO event_attendees (event_id, email) VALUES ($event_id, '$email')";
                    $conn->query($sql);
                }
            }
            
            return $event_id;
        }
        
        return false;
    }
    
    public function getEventsToProcess() {
        $conn = $this->db->getConnection();
        // Fetch candidate events that are scheduled and not yet emailed.
        // We'll do the time comparison in PHP to avoid timezone mismatches between client input and server.
        $sql = "SELECT e.*, f.unique_code as form_code, f.title as form_title 
                FROM events e 
                JOIN forms f ON e.form_id = f.id 
                WHERE e.status = 'scheduled' 
                AND e.emails_sent = 0";

        $result = $conn->query($sql);
        $events = [];

        $now_ts = time();
        while ($row = $result->fetch_assoc()) {
            // Normalize end_time to a timestamp (best-effort). If parsing fails, skip.
            $end_ts = strtotime($row['end_time']);
            if ($end_ts === false) {
                // couldn't parse end_time; skip this event
                continue;
            }

            // If the event has ended (end_time <= now), include it for processing.
            if ($end_ts <= $now_ts) {
                $events[] = $row;
            }
        }

        return $events;
    }
    
    public function getEventAttendees($event_id) {
        $conn = $this->db->getConnection();
        $event_id = (int)$event_id;
        
        $sql = "SELECT email FROM event_attendees WHERE event_id = $event_id";
        $result = $conn->query($sql);
        $attendees = [];
        
        while ($row = $result->fetch_assoc()) {
            $attendees[] = $row['email'];
        }
        
        return $attendees;
    }
    
    public function markEmailsSent($event_id) {
        $conn = $this->db->getConnection();
        $event_id = (int)$event_id;
        
        $sql = "UPDATE events SET emails_sent = 1, status = 'completed' WHERE id = $event_id";
        return $conn->query($sql);
    }
    
    public function getAllEvents() {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT e.*, f.title as form_title, 
                (SELECT COUNT(*) FROM event_attendees ea WHERE ea.event_id = e.id) as attendee_count
                FROM events e 
                JOIN forms f ON e.form_id = f.id 
                ORDER BY e.start_time DESC";
        
        $result = $conn->query($sql);
        $events = [];
        
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        
        return $events;
    }
    
    public function deleteEvent($event_id) {
        $conn = $this->db->getConnection();
        $event_id = (int)$event_id;
        
        // Delete attendees first
        $conn->query("DELETE FROM event_attendees WHERE event_id = $event_id");
        
        // Then delete event
        $sql = "DELETE FROM events WHERE id = $event_id";
        return $conn->query($sql);
    }
}
?>