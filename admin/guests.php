<?php
// admin/guests.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireRole('Admin');

// Fetch guests combined with total bookings history count
$stmt = $pdo->query("
    SELECT g.*, u.username, 
           (SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.user_id) as total_bookings
    FROM guests g
    JOIN users u ON g.user_id = u.user_id
    ORDER BY g.created_at DESC
");
$guests = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h2 class="page-title mb-0"><i class="fa-solid fa-address-book me-2"></i>Guest Directory</h2>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <?php if(count($guests) === 0): ?>
            <p class="text-muted text-center py-4">No guests registered yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Guest Info</th>
                            <th>Contact Details</th>
                            <th>Identity Proof</th>
                            <th>Bookings History</th>
                            <th>Reg Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($guests as $g): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($g['full_name']) ?></div>
                                    <div class="small text-muted">User: <?= htmlspecialchars($g['username']) ?></div>
                                </td>
                                <td>
                                    <div class="small"><i class="fa-regular fa-envelope me-1"></i> <?= htmlspecialchars($g['email']) ?></div>
                                    <div class="small"><i class="fa-solid fa-phone me-1"></i> <?= htmlspecialchars($g['phone']) ?></div>
                                </td>
                                <td>
                                    <?php if($g['id_proof_type']): ?>
                                        <div class="badge bg-secondary"><?= htmlspecialchars($g['id_proof_type']) ?></div>
                                        <div class="small text-muted mt-1"><?= htmlspecialchars($g['id_proof_number']) ?></div>
                                    <?php else: ?>
                                        <em class="text-danger small">Not Provided</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info rounded-pill px-3"><?= $g['total_bookings'] ?> Bookings</span>
                                </td>
                                <td class="text-muted small"><?= date('M d, Y', strtotime($g['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
