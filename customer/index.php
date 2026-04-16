<?php
// customer/index.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireRole('Customer');

$user_id = $_SESSION['user_id'];

// Get Guest info if missing (to prompt profile completion)
$stmt = $pdo->prepare("SELECT * FROM guests WHERE user_id = ?");
$stmt->execute([$user_id]);
$guest = $stmt->fetch();

// Get their Bookings
$stmt = $pdo->prepare("
    SELECT b.*, r.room_type, r.price_per_night, p.payment_status 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.room_id 
    LEFT JOIN payments p ON b.booking_id = p.booking_id
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-12 text-center text-md-start">
        <h2 class="page-title"><i class="fa-solid fa-home me-2"></i>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
    </div>
</div>

<?php if (!$guest): ?>
    <div class="alert alert-warning">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> Please complete your <a href="profile.php" class="alert-link">Guest Profile</a> before booking a room.
    </div>
<?php endif; ?>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white pb-0 border-0 pt-3 text-end d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">My Bookings</h5>
        <a href="book.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> New Booking</a>
    </div>
    <div class="card-body">
        <?php if (count($bookings) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Room Type</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Total Amount</th>
                            <th>Booking Status</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($bookings as $b): ?>
                            <tr>
                                <td>#<?= $b['booking_id'] ?></td>
                                <td><span class="badge bg-light text-dark border"><i class="fa-solid fa-bed me-1"></i><?= $b['room_type'] ?></span></td>
                                <td><?= date('M d, Y', strtotime($b['check_in'])) ?></td>
                                <td><?= date('M d, Y', strtotime($b['check_out'])) ?></td>
                                <td>$<?= number_format($b['total_amount'], 2) ?></td>
                                <td>
                                    <?php 
                                        $bClass = $b['status'] == 'Confirmed' ? 'success' : ($b['status'] == 'Pending' ? 'warning' : 'danger');
                                        echo "<span class='badge bg-{$bClass}'>{$b['status']}</span>";
                                    ?>
                                </td>
                                <td>
                                    <?php if ($b['payment_status']): ?>
                                        <?php 
                                            $pClass = $b['payment_status'] == 'Completed' ? 'success' : 'secondary';
                                            echo "<span class='badge bg-{$pClass}'>{$b['payment_status']}</span>";
                                        ?>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark">Unpaid</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-suitcase fa-3x mb-3 text-secondary"></i>
                <p class="mb-0">You have no bookings yet. Time for a vacation!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
