<?php
// receptionist/checkout.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireRole(['Receptionist', 'Admin']);

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $b_id = (int)$_POST['booking_id'];
    $method = $_POST['payment_method'];
    $amount = (float)$_POST['amount'];
    
    try {
        $pdo->beginTransaction();
        
        // 1. Record Payment
        $stmt = $pdo->prepare("INSERT INTO payments (booking_id, amount, payment_method, payment_status) VALUES (?, ?, ?, 'Completed')");
        $stmt->execute([$b_id, $amount, $method]);
        
        // 2. Update booking and room (mocking room freeup here)
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'Completed' WHERE booking_id = ?");
        $stmt->execute([$b_id]);
        
        $pdo->commit();
        header("Location: index.php?msg=CheckoutComplete");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = "Error processing payment.";
    }
}

// Fetch booking data
$stmt = $pdo->prepare("
    SELECT b.*, u.username, g.full_name, g.email, g.phone, r.room_type, r.price_per_night 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.room_id
    JOIN users u ON b.user_id = u.user_id
    LEFT JOIN guests g ON u.user_id = g.user_id
    WHERE b.booking_id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    echo "Invalid Booking ID";
    exit();
}
?>
<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h2 class="page-title mb-0"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Process Checkout & Billing</h2>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Cancel</a>
</div>

<?php if ($msg): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="row align-items-start">
    <div class="col-md-7">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white pt-3 border-0 fw-bold">Invoice Details</div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted fw-bold small text-uppercase">Guest</div>
                    <div class="col-sm-8 fw-medium"><?= htmlspecialchars($booking['full_name'] ?: $booking['username']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted fw-bold small text-uppercase">Room Type</div>
                    <div class="col-sm-8"><?= $booking['room_type'] ?> ($<?= $booking['price_per_night'] ?>/night)</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted fw-bold small text-uppercase">Dates</div>
                    <div class="col-sm-8">
                        <?= date('M d, Y', strtotime($booking['check_in'])) ?> 
                        <i class="fa-solid fa-arrow-right mx-2 text-muted"></i> 
                        <?= date('M d, Y', strtotime($booking['check_out'])) ?>
                    </div>
                </div>
                <div class="row mb-3 pt-3 border-top">
                    <div class="col-sm-4 text-muted fw-bold small text-uppercase">Total Amount Due</div>
                    <div class="col-sm-8 text-primary fw-bold fs-4">$<?= number_format($booking['total_amount'], 2) ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-5">
        <div class="card shadow-sm border-0 border-top border-primary border-4">
            <div class="card-body p-4">
                <h5 class="mb-4">Process Payment</h5>
                <form action="checkout.php?booking_id=<?= $booking_id ?>" method="POST">
                    <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
                    <input type="hidden" name="amount" value="<?= $booking['total_amount'] ?>">
                    
                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold small text-uppercase">Payment Method</label>
                        <select name="payment_method" class="form-select border-primary" required>
                            <option value="Cash">Cash</option>
                            <option value="Card">Credit/Debit Card</option>
                            <option value="UPI">UPI</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100 fw-bold py-2">
                        <i class="fa-solid fa-check-circle me-1"></i> Confirm Final Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
