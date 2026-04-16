<?php
// admin/index.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Secure this route
requireRole('Admin');

// Fetch basic stats
$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'rooms' => $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn(),
    'bookings' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'revenue' => $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'Completed'")->fetchColumn() ?: 0
];
?>
<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
    <h2 class="page-title mb-0"><i class="fa-solid fa-gauge me-2"></i>Admin Dashboard</h2>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 text-uppercase fw-bold">Total Users</h6>
                        <h2 class="mb-0 fw-bold"><?= $stats['users'] ?></h2>
                    </div>
                    <i class="fa-solid fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 text-uppercase fw-bold">Total Revenue</h6>
                        <h2 class="mb-0 fw-bold">$<?= number_format($stats['revenue'], 2) ?></h2>
                    </div>
                    <i class="fa-solid fa-dollar-sign fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 text-uppercase fw-bold">All Bookings</h6>
                        <h2 class="mb-0 fw-bold"><?= $stats['bookings'] ?></h2>
                    </div>
                    <i class="fa-solid fa-calendar-check fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 text-uppercase fw-bold">Total Rooms</h6>
                        <h2 class="mb-0 fw-bold"><?= $stats['rooms'] ?></h2>
                    </div>
                    <i class="fa-solid fa-bed fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Manage Hotel Operations</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush rounded-bottom">
                    <a href="rooms.php" class="list-group-item list-group-item-action py-3">
                        <i class="fa-solid fa-door-open fa-fw me-2 text-primary"></i> <span class="fw-medium">Room Management</span>
                        <div class="small text-muted ms-4">Add, edit, and organize hotel rooms.</div>
                    </a>
                    <a href="users.php" class="list-group-item list-group-item-action py-3">
                        <i class="fa-solid fa-user-shield fa-fw me-2 text-primary"></i> <span class="fw-medium">User Management</span>
                        <div class="small text-muted ms-4">Manage admins, receptionists, and customers.</div>
                    </a>
                    <a href="guests.php" class="list-group-item list-group-item-action py-3">
                        <i class="fa-solid fa-id-card fa-fw me-2 text-primary"></i> <span class="fw-medium">Guest Records</span>
                        <div class="small text-muted ms-4">View comprehensive guest profiles & ID info.</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header border-0 pb-0">
                <span>Recent System Activity</span>
            </div>
            <div class="card-body">
                <p class="text-muted small">This section can later be hooked to a logging table, or show the newest bookings in realtime.</p>
                <!-- Placeholder for Recent Bookings Overview -->
                <?php
                $recent = $pdo->query("SELECT b.booking_id, b.status, u.username, b.check_in FROM bookings b JOIN users u ON b.user_id = u.user_id ORDER BY b.created_at DESC LIMIT 4")->fetchAll();
                if ($recent) {
                    echo "<ul class='list-group list-group-flush'>";
                    foreach ($recent as $r) {
                        $badge = $r['status'] == 'Confirmed' ? 'success' : ($r['status'] == 'Pending' ? 'warning' : 'secondary');
                        echo "<li class='list-group-item px-0 d-flex justify-content-between align-items-center'>";
                        echo "<span>Booking #{$r['booking_id']} by <strong>{$r['username']}</strong></span>";
                        echo "<span class='badge bg-{$badge} rounded-pill'>{$r['status']}</span>";
                        echo "</li>";
                    }
                    echo "</ul>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
