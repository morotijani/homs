<?php
// customer/book.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireRole('Customer');

$msg = '';
$user_id = $_SESSION['user_id'];

// Get purely available rooms
$stmt = $pdo->query("SELECT * FROM rooms WHERE status = 'Available'");
$available_rooms = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = (int)$_POST['room_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    
    // Server-side validation for dates
    $in_date = new DateTime($check_in);
    $out_date = new DateTime($check_out);
    
    if ($in_date >= $out_date) {
        $msg = "Error: Check-out date must be after Check-in date.";
    } else {
        // Look up room cost
        $stmt = $pdo->prepare("SELECT price_per_night FROM rooms WHERE room_id = ? AND status = 'Available'");
        $stmt->execute([$room_id]);
        $room = $stmt->fetch();
        
        if ($room) {
            $days = $out_date->diff($in_date)->days;
            $total_amount = $days * $room['price_per_night'];
            
            // In a real production system, you'd verify if the room is taken for EXACTLY those dates.
            // For this student MS, we'll assume if it's "Available", it can be booked for the requested future dates.
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, room_id, check_in, check_out, total_amount, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
            if ($stmt->execute([$user_id, $room_id, $check_in, $check_out, $total_amount])) {
                header("Location: index.php");
                exit();
            }
        } else {
            $msg = "Error: Room is no longer available.";
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h2 class="page-title mb-0"><i class="fa-solid fa-calendar-plus me-2"></i>Request a Booking</h2>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <?php if ($msg): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
                <?php endif; ?>
                
                <form action="book.php" method="POST">
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Select Room</label>
                        <select name="room_id" class="form-select" required>
                            <option value="">-- Choose a available room --</option>
                            <?php foreach($available_rooms as $r): ?>
                                <option value="<?= $r['room_id'] ?>">
                                    <?= htmlspecialchars($r['room_type']) ?> Room (<?= $r['room_id'] ?>) - $<?= number_format($r['price_per_night'], 2) ?>/night
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase">Check In Date</label>
                            <input type="date" name="check_in" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase">Check Out Date</label>
                            <input type="date" name="check_out" class="form-control" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>
                    </div>
                    
                    <div class="alert alert-info small rounded-3">
                        <i class="fa-solid fa-circle-info me-1"></i> <strong>Note:</strong> Pricing will be calculated dynamically based on duration of stay. You will pay upon receptionist confirmation.
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold text-uppercase font-monospace tracking-wide">Submit Booking Request</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
