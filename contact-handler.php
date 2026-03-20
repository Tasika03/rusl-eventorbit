<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

// Block non-logged-in users
if (empty($_SESSION['user_id'])) {
    setcookie('redirect_after_login', 'contact.html', time() + 300, '/');
    header('Location: auth/login.php?msg=login_required');
    exit;
}

$name    = sanitize($_POST['name']    ?? '');
$email   = sanitize($_POST['email']   ?? '');
$subject = sanitize($_POST['subject'] ?? '');
$message = sanitize($_POST['message'] ?? '');

$errors = [];
if (strlen($name) < 2)                          $errors[] = 'Please enter your name.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
if (strlen($subject) < 3)                       $errors[] = 'Please enter a subject.';
if (strlen($message) < 10)                      $errors[] = 'Message must be at least 10 characters.';

if (empty($errors)) {
    $pdo->prepare('INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)')
        ->execute([$name, $email, $subject, $message]);
    header('Location: contact.html?status=success');
    exit;
} else {
    $errorMsg = urlencode(implode('|', $errors));
    header('Location: contact.html?status=error&msg=' . $errorMsg);
    exit;
}