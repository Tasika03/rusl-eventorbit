<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
redirectIfLoggedIn();

$error = '';
// Show message if redirected from a protected page
if (isset($_GET['msg']) && $_GET['msg'] === 'login_required') {
    $error = 'Please log in to continue.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email']    ?? '');
    $password =          $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Redirect back to original page if set, otherwise go to dashboard
        setcookie('eo_user', $user['username'], time() + (86400 * 7), '/');

        // Redirect back to original page if set, otherwise go to dashboard
        $redirect = $_COOKIE['redirect_after_login'] ?? 'dashboard.php';
        setcookie('redirect_after_login', '', time() - 3600, '/'); // clear it

        setFlash('success', 'Welcome back, ' . $user['username'] . '!');
        header('Location: ../dashboard.php');
        exit;
    } else {
        $error = 'Incorrect email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RUSL EventOrbit – Login</title>
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
                <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                <li class="nav-item"><a class="nav-link active" href="login.php">Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<div style="background:linear-gradient(135deg,#8B1A1A,#c0392b);color:white;padding:30px 0 22px;">
    <div class="container">
        <h2 class="fw-bold mb-1">Login</h2>
        <p class="mb-0 opacity-75">Sign in to your EventOrbit account</p>
    </div>
</div>

<div class="container"><div class="form-section">
    <h2>Welcome Back</h2>

    <?php showFlash(); ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-4">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-rusl px-5">Login</button>
        <a href="register.php" class="btn btn-outline-secondary ms-2">Create account</a>
    </form>
</div></div>

<footer><div class="container text-center">
    <div class="footer-brand">🎓 EventOrbit</div>
    <p class="small mb-0">© 2026 RUSL EventOrbit | All Rights Reserved</p>
</div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>