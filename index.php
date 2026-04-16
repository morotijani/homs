<?php
// index.php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Fetch a summary of available rooms
$stmt = $pdo->query("SELECT room_type, price_per_night, COUNT(room_id) as total_available FROM rooms WHERE status = 'Available' GROUP BY room_type, price_per_night");
$available_rooms = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<div class="row mb-5">
    <div class="col-lg-8 mx-auto text-center">
        <h1 class="display-4 fw-bold text-primary mb-3">Welcome to LuxeStay</h1>
        <p class="lead text-muted mb-4">Experience the perfect blend of comfort and luxury. Whether business or leisure, we have the right room for you.</p>
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-primary btn-lg px-4 me-2">Book Your Stay Now</a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <h3 class="mb-4 border-bottom pb-2">Our Available accommodations</h3>
    </div>
    
    <?php if (count($available_rooms) > 0): ?>
        <?php foreach ($available_rooms as $room): ?>
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fa-solid fa-bed fa-3x text-secondary"></i>
                        </div>
                        <h4 class="card-title fw-bold"><?= htmlspecialchars($room['room_type']) ?> Room</h4>
                        <h5 class="text-primary fw-bold mb-3">$<?= number_format($room['price_per_night'], 2) ?> <small class="text-muted fw-normal">/ night</small></h5>
                        <p class="text-muted"><i class="fa-solid fa-check-circle text-success me-1"></i> <?= $room['total_available'] ?> Rooms available</p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12 text-center text-muted py-5">
            <i class="fa-solid fa-calendar-times fa-4x mb-3 text-secondary"></i>
            <h5>We are fully booked right now.</h5>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
