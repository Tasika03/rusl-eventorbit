<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// If not a POST request, send back to form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add-event.html');
    exit;
}

// Must be logged in
if (empty($_SESSION['user_id'])) {
    header('Location: auth/login.php?msg=login_required');
exit;
}

$eventName   = sanitize($_POST['eventName']        ?? '');
$eventDate   = sanitize($_POST['eventDate']        ?? '');
$category    = sanitize($_POST['eventCategory']    ?? '');
$faculty     = sanitize($_POST['eventFaculty']     ?? '');
$location    = sanitize($_POST['eventLocation']    ?? '');
$accessType  = sanitize($_POST['accessType']       ?? '');
$description = sanitize($_POST['eventDescription'] ?? '');

$errors = [];
if (!$eventName)               $errors[] = 'Event name is required.';
if (!$eventDate)               $errors[] = 'Date is required.';
if (!$category)                $errors[] = 'Category is required.';
if (!$faculty)                 $errors[] = 'Faculty is required.';
if (!$location)                $errors[] = 'Location is required.';
if (!$accessType)              $errors[] = 'Access type is required.';
if (strlen($description) < 20) $errors[] = 'Description must be at least 20 characters.';

// Handle image upload
$imagePath = null;
if (!empty($_FILES['eventImage']['name'])) {
    $file = $_FILES['eventImage'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif'])) {
        $errors[] = 'Invalid image format.';
    } elseif ($file['size'] > 5 * 1024 * 1024) {
        $errors[] = 'Image too large. Max 5MB.';
    } else {
        $uploadDir = 'images/events/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $newName = uniqid('evt_') . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $uploadDir . $newName);
        $imagePath = $uploadDir . $newName;
    }
}

if (empty($errors)) {
    $pdo->prepare(
        'INSERT INTO events
         (user_id, event_name, event_date, category, faculty, location, access_type, description, image_path, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "pending")'
    )->execute([
        $_SESSION['user_id'], $eventName, $eventDate, $category,
        $faculty, $location, $accessType, $description, $imagePath
    ]);
    header('Location: add-event.html?status=success&name=' . urlencode($eventName));
    exit;
} else {
    $errorMsg = urlencode(implode('|', $errors));
    header('Location: add-event.html?status=error&msg=' . $errorMsg);
    exit;
}