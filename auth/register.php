<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
redirectIfLoggedIn();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email    = sanitize($_POST['email']    ?? '');
    $password =          $_POST['password'] ?? '';
    $confirm  =          $_POST['confirm']  ?? '';

    if (strlen($username) < 3)                     $errors[] = 'Username must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
    if (strlen($password) < 6)                     $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)                    $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Username or email already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)')
                ->execute([$username, $email, $hash]);
            setFlash('success', 'Account created! Please log in.');
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RUSL EventOrbit – Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="../index.html">🎓 EventOrbit</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="../index.html">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="../events.html">Events</a></li>
                <li class="nav-item"><a class="nav-link active" href="register.php">Register</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<div style="background:linear-gradient(135deg,#8B1A1A,#c0392b);color:white;padding:30px 0 22px;">
    <div class="container">
        <h2 class="fw-bold mb-1">Create Account</h2>
        <p class="mb-0 opacity-75">Join EventOrbit to submit events</p>
    </div>
</div>

<div class="container"><div class="form-section">
    <h2>Register</h2>
    <p class="subtitle">All fields marked <span style="color:#8B1A1A">*</span> are required.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username *</label>
            <input type="text" name="username" class="form-control" value="<?= sanitize($_POST['username'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-4">
            <label class="form-label">Confirm Password *</label>
            <input type="password" name="confirm" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-rusl px-5">Create Account</button>
        <a href="login.php" class="btn btn-outline-secondary ms-2">Already have an account?</a>
    </form>
</div></div>

<footer><div class="container text-center">
    <div class="footer-brand">🎓 EventOrbit</div>
    <p class="small mb-0">© 2026 RUSL EventOrbit | All Rights Reserved</p>
</div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>