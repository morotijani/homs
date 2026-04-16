<?php
// admin/rooms.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireRole('Admin');

$msg = '';

// Handle Create / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $type = $_POST['room_type'];
        $price = (float)$_POST['price_per_night'];
        $status = $_POST['status'];
        
        $stmt = $pdo->prepare("INSERT INTO rooms (room_type, price_per_night, status) VALUES (?, ?, ?)");
        if ($stmt->execute([$type, $price, $status])) {
            $msg = "Room added successfully.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)$_POST['room_id'];
        // Ensure no active bookings use this room (Simple demo implementation: delete cascades or warns)
        try {
            $stmt = $pdo->prepare("DELETE FROM rooms WHERE room_id = ?");
            $stmt->execute([$id]);
            $msg = "Room deleted successfully.";
        } catch (\PDOException $e) {
            $msg = "Error: Cannot delete room. It might be linked to existing bookings.";
        }
    }
}

$rooms = $pdo->query("SELECT * FROM rooms ORDER BY room_id DESC")->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h2 class="page-title mb-0"><i class="fa-solid fa-door-open me-2"></i>Manage Rooms</h2>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
</div>

<?php if ($msg): ?>
    <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white pt-3 pb-0 border-0 fw-bold">Add New Room</div>
            <div class="card-body">
                <form action="rooms.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Room Type</label>
                        <select name="room_type" class="form-select" required>
                            <option value="Single">Single Room</option>
                            <option value="Deluxe">Deluxe Room</option>
                            <option value="Suite">Suite</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Price / Night ($)</label>
                        <input type="number" step="0.01" name="price_per_night" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Initial Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Available">Available</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Room</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Room ID</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($rooms as $r): ?>
                            <tr>
                                <td>#<?= $r['room_id'] ?></td>
                                <td><?= htmlspecialchars($r['room_type']) ?></td>
                                <td>$<?= number_format($r['price_per_night'], 2) ?></td>
                                <td>
                                    <?php 
                                        $rs = $r['status'] == 'Available' ? 'success' : ($r['status'] == 'Occupied' ? 'danger' : 'warning');
                                        echo "<span class='badge bg-{$rs}'>{$r['status']}</span>";
                                    ?>
                                </td>
                                <td>
                                    <form action="rooms.php" method="POST" onsubmit="return confirm('Delete this room?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="room_id" value="<?= $r['room_id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($rooms)) echo "<tr><td colspan='5' class='text-center py-3'>No rooms found.</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
