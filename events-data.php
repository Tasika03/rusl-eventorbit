<?php
// events-data.php
// Returns approved events as JSON for events.html to display

require_once 'includes/db.php';

header('Content-Type: application/json');

$pdo->query('DELETE FROM events WHERE event_date < CURDATE()');
$stmt = $pdo->query(
    'SELECT e.*, u.username 
     FROM events e 
     JOIN users u ON e.user_id = u.id 
     WHERE e.status = "approved" AND e.event_date >= CURDATE()
     ORDER BY e.event_date ASC'
);

$events = $stmt->fetchAll();

// Format for JavaScript
$result = [];
foreach ($events as $ev) {
    $result[] = [
        'id'          => $ev['id'],
        'title'       => $ev['event_name'],
        'date'        => date('F j, Y', strtotime($ev['event_date'])),
        'category'    => $ev['category'],
        'faculty'     => $ev['faculty'],
        'location'    => $ev['location'],
        'access'      => $ev['access_type'],
        'description' => $ev['description'],
        'image'       => $ev['image_path'] ?? 'images/default.jpg',
        'submittedBy' => $ev['username'],
    ];
}

echo json_encode($result);