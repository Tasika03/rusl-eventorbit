<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
requireLogin();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM events WHERE id = ? AND user_id = ?');
    $stmt->execute([$_GET['delete'], $_SESSION['user_id']]);
    header('Location: dashboard.php?msg=deleted');
    exit;
}

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $editId      = (int) $_POST['edit_id'];
    $eventName   = sanitize($_POST['eventName']        ?? '');
    $eventDate   = sanitize($_POST['eventDate']        ?? '');
    $category    = sanitize($_POST['eventCategory']    ?? '');
    $faculty     = sanitize($_POST['eventFaculty']     ?? '');
    $location    = sanitize($_POST['eventLocation']    ?? '');
    $accessType  = sanitize($_POST['accessType']       ?? '');
    $description = sanitize($_POST['eventDescription'] ?? '');

    $pdo->prepare(
        'UPDATE events SET event_name=?, event_date=?, category=?, faculty=?, location=?, access_type=?, description=?, status="pending"
         WHERE id=? AND user_id=?'
    )->execute([
        $eventName, $eventDate, $category, $faculty,
        $location, $accessType, $description,
        $editId, $_SESSION['user_id']
    ]);

    header('Location: dashboard.php?msg=updated');
    exit;
}

// Fetch events for edit modal
$editEvent = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM events WHERE id = ? AND user_id = ?');
    $stmt->execute([$_GET['edit'], $_SESSION['user_id']]);
    $editEvent = $stmt->fetch();
}

// Fetch all user events
$stmt = $pdo->prepare('SELECT * FROM events WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$myEvents = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RUSL EventOrbit – Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.html">🎓 EventOrbit</a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="events.html">Events</a></li>
                <li class="nav-item"><a class="nav-link" href="add-event.html">Add Event</a></li>
                <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="auth/logout.php">
                        Logout (<?= htmlspecialchars($_SESSION['username']) ?>)
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div style="background:linear-gradient(135deg,#8B1A1A,#c0392b);color:white;padding:30px 0 22px;">
    <div class="container">
        <h2 class="fw-bold mb-1">My Dashboard</h2>
        <p class="mb-0 opacity-75">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
    </div>
</div>

<div class="container mt-5 mb-5">

    <?php showFlash(); ?>

    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-success">✅ Event deleted successfully.</div>
        <?php elseif ($_GET['msg'] === 'updated'): ?>
            <div class="alert alert-success">✅ Event updated. It will be reviewed again before appearing on the events page.</div>
        <?php endif; ?>
    <?php endif; ?>

    <h4 class="mb-4">My Submitted Events
        <span class="badge" style="background:#8B1A1A;"><?= count($myEvents) ?></span>
    </h4>

    <?php if (empty($myEvents)): ?>
        <div class="alert alert-info">
            No events submitted yet.
            <a href="add-event.html" class="alert-link">Submit your first event →</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Faculty</th>
                        <th>Access</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myEvents as $i => $ev): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($ev['event_name']) ?></td>
                        <td><?= $ev['event_date'] ?></td>
                        <td><?= $ev['category'] ?></td>
                        <td><?= $ev['faculty'] ?></td>
                        <td>
                            <?php if ($ev['access_type'] === 'Public'): ?>
                                <span class="badge bg-success">Public</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Private</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($ev['status'] === 'approved'): ?>
                                <span class="badge bg-success">✅ Approved</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">⏳ Pending Review</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="dashboard.php?edit=<?= $ev['id'] ?>"
                               class="btn btn-sm btn-outline-primary me-1">Edit</a>
                            <a href="dashboard.php?delete=<?= $ev['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <a href="add-event.html" class="btn btn-rusl mt-3">+ Add New Event</a>
</div>

<!-- Edit Modal -->
<?php if ($editEvent): ?>
<div class="modal fade show" style="display:block;background:rgba(0,0,0,0.5);" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#8B1A1A;color:white;">
                <h5 class="modal-title">Edit Event</h5>
                <a href="dashboard.php" class="btn-close" style="filter:invert(1)"></a>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="edit_id" value="<?= $editEvent['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Event Name *</label>
                        <input type="text" name="eventName" class="form-control"
                               value="<?= htmlspecialchars($editEvent['event_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Event Date *</label>
                        <input type="date" name="eventDate" class="form-control"
                               value="<?= $editEvent['event_date'] ?>" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Category *</label>
                            <select name="eventCategory" class="form-select" required>
                                <?php foreach (['Academic','Cultural','Sports','Workshop'] as $cat): ?>
                                    <option value="<?= $cat ?>" <?= $editEvent['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Faculty *</label>
                            <select name="eventFaculty" class="form-select" required>
                                <?php foreach (['FAS'=>'Faculty of Applied Sciences','FOT'=>'Faculty of Technology','MGT'=>'Faculty of Management Studies','FSSH'=>'Faculty of Social Sciences & Humanities','FOA'=>'Faculty of Agriculture','FMAS'=>'Faculty of Medicine & Allied Sciences','University'=>'University Wide'] as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $editEvent['faculty'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location *</label>
                        <input type="text" name="eventLocation" class="form-control"
                               value="<?= htmlspecialchars($editEvent['location']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Access Type *</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="accessType"
                                       value="Public" <?= $editEvent['access_type'] === 'Public' ? 'checked' : '' ?>>
                                <label class="form-check-label">Public</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="accessType"
                                       value="Private" <?= $editEvent['access_type'] === 'Private' ? 'checked' : '' ?>>
                                <label class="form-check-label">Private</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea name="eventDescription" class="form-control" rows="4" required><?= htmlspecialchars($editEvent['description']) ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-rusl px-4">Save Changes</button>
                        <a href="dashboard.php" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<footer>
    <div class="container text-center">
        <div class="footer-brand">🎓 EventOrbit</div>
        <p class="small mb-3">Rajarata University of Sri Lanka – Department of Computing</p>
        <hr>
        <p class="small mb-2">
            <a href="index.html">Home</a> &nbsp;|&nbsp;
            <a href="events.html">Events</a> &nbsp;|&nbsp;
            <a href="contact.html">Contact</a>
        </p>
        <p class="small mb-0">© 2026 RUSL EventOrbit | All Rights Reserved</p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>