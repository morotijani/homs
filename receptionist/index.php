<?php
// receptionist/index.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireRole(['Receptionist', 'Admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $booking_id = (int)$_POST['booking_id'];
        $new_status = in_array($_POST['status'], ['Pending', 'Confirmed', 'Cancelled', 'Completed']) ? $_POST['status'] : 'Pending';
        
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $stmt->execute([$new_status, $booking_id]);
        
        // If "Completed", we can free up the room here, but for simplicity, we focus on the booking update.
        header("Location: index.php?msg=StatusUpdated");
        exit();
    }
}

// Fetch all active bookings for front desk
$stmt = $pdo->query("
    SELECT b.*, u.username, g.full_name, r.room_type 
    FROM bookings b 
    JOIN users u ON b.user_id = u.user_id 
    LEFT JOIN guests g ON u.user_id = g.user_id
    JOIN rooms r ON b.room_id = r.room_id
    ORDER BY b.check_in ASC
");
$todays_bookings = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
    <h2 class="page-title mb-0"><i class="fa-solid fa-desktop me-2"></i>Receptionist Station</h2>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">Booking status has been updated successfully.</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white pt-3 pb-0 border-0">
        <h5 class="fw-bold">All Bookings Overview</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Guest Name</th>
                        <th>Room</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($todays_bookings as $tb): ?>
                        <tr>
                            <td>#<?= $tb['booking_id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($tb['full_name'] ?: $tb['username']) ?></strong>
                            </td>
                            <td><?= $tb['room_type'] ?></td>
                            <td><?= date('M d, Y', strtotime($tb['check_in'])) ?></td>
                            <td><?= date('M d, Y', strtotime($tb['check_out'])) ?></td>
                            <td>
                                <?php 
                                    $sClass = $tb['status'] == 'Confirmed' ? 'success' : ($tb['status'] == 'Pending' ? 'warning' : ($tb['status'] == 'Cancelled' ? 'danger' : 'info'));
                                    echo "<span class='badge bg-{$sClass}'>{$tb['status']}</span>";
                                ?>
                            </td>
                            <td>
                                <form method="POST" action="index.php" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="booking_id" value="<?= $tb['booking_id'] ?>">
                                    <select name="status" class="form-select form-select-sm" style="width: auto;">
                                        <option value="Pending" <?= $tb['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="Confirmed" <?= $tb['status'] == 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="Cancelled" <?= $tb['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        <option value="Completed" <?= $tb['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                </form>
                                <?php if ($tb['status'] === 'Confirmed'): ?>
                                    <a href="checkout.php?booking_id=<?= $tb['booking_id'] ?>" class="btn btn-sm btn-success mt-2 w-100"><i class="fa-solid fa-file-invoice-dollar me-1"></i> Checkout & Bill</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
