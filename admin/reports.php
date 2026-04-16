<?php
// admin/reports.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireRole('Admin');

// 1. Occupancy Rate Calculation
$total_rooms = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$occupied_rooms = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'Occupied'")->fetchColumn();
$occupancy_rate = $total_rooms > 0 ? round(($occupied_rooms / $total_rooms) * 100, 1) : 0;

// 2. Revenue by Payment Method
$revenue_breakdown = $pdo->query("
    SELECT payment_method, SUM(amount) as total_collected, COUNT(*) as tx_count 
    FROM payments 
    WHERE payment_status = 'Completed' 
    GROUP BY payment_method
")->fetchAll();

// 3. Overall Revenue
$total_revenue = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'Completed'")->fetchColumn() ?: 0;

// 4. Guest Statistics
$total_guests = $pdo->query("SELECT COUNT(*) FROM guests")->fetchColumn();
$most_frequent_guests = $pdo->query("
    SELECT g.full_name, COUNT(b.booking_id) as stay_count, SUM(p.amount) as total_spent
    FROM guests g
    JOIN bookings b ON g.user_id = b.user_id
    LEFT JOIN payments p ON b.booking_id = p.booking_id AND p.payment_status = 'Completed'
    GROUP BY g.guest_id
    ORDER BY stay_count DESC, total_spent DESC
    LIMIT 5
")->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h2 class="page-title mb-0"><i class="fa-solid fa-chart-line me-2"></i>Detailed Reports</h2>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back Dashboard</a>
</div>

<!-- Key Performance Indicators (KPIs) -->
<div class="row g-4 mb-4">
    <!-- Occupancy KPI -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center py-4">
                <div class="mb-3">
                    <i class="fa-solid fa-hotel fa-3x <?= $occupancy_rate > 70 ? 'text-success' : 'text-primary' ?>"></i>
                </div>
                <h6 class="text-muted text-uppercase fw-bold">Live Occupancy Rate</h6>
                <h1 class="display-5 fw-bold mb-0"><?= $occupancy_rate ?>%</h1>
                <p class="text-muted small mt-2"><?= $occupied_rooms ?> out of <?= $total_rooms ?> rooms occupied</p>
                <div class="progress mt-3" style="height: 10px;">
                    <div class="progress-bar <?= $occupancy_rate > 70 ? 'bg-success' : 'bg-primary' ?>" role="progressbar" style="width: <?= $occupancy_rate ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Revenue KPI -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center py-4">
                <div class="mb-3">
                    <i class="fa-solid fa-sack-dollar fa-3x text-success"></i>
                </div>
                <h6 class="text-muted text-uppercase fw-bold">Total Revenue</h6>
                <h1 class="display-5 fw-bold mb-0 text-success">$<?= number_format($total_revenue, 2) ?></h1>
                <p class="text-muted small mt-2">Lifetime validated transactions</p>
            </div>
        </div>
    </div>
    
    <!-- Total Guests KPI -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center py-4">
                <div class="mb-3">
                    <i class="fa-solid fa-address-book fa-3x text-info"></i>
                </div>
                <h6 class="text-muted text-uppercase fw-bold">Registered Guests</h6>
                <h1 class="display-5 fw-bold mb-0 text-info"><?= $total_guests ?></h1>
                <p class="text-muted small mt-2">Total distinct profiles safely stored</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Revenue Breakdown Chart -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white pt-3 border-0 fw-bold pb-0">Revenue Breakdown by Payment Method</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mt-3">
                        <thead class="bg-light">
                            <tr>
                                <th>Payment Method</th>
                                <th>Transactions</th>
                                <th>Total Collected</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($revenue_breakdown as $rev): ?>
                                <tr>
                                    <td>
                                        <?php if($rev['payment_method'] == 'Card'): ?>
                                            <i class="fa-regular fa-credit-card me-2 text-primary"></i> Credit/Debit Card
                                        <?php elseif($rev['payment_method'] == 'Cash'): ?>
                                            <i class="fa-solid fa-money-bill-wave me-2 text-success"></i> Cash
                                        <?php else: ?>
                                            <i class="fa-solid fa-mobile-screen me-2 text-info"></i> UPI
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-secondary rounded-pill px-3"><?= $rev['tx_count'] ?></span></td>
                                    <td class="fw-bold">$<?= number_format($rev['total_collected'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(empty($revenue_breakdown)): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">No revenue data available yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Guests / VIPs -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white pt-3 border-0 fw-bold pb-0">Top Guests (Most Frequent)</div>
            <div class="card-body">
                <div class="list-group list-group-flush mt-2">
                    <?php foreach($most_frequent_guests as $index => $vip): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <div class="d-flex align-items-center">
                                <h4 class="mb-0 me-3 mt-1 text-muted">#<?= $index + 1 ?></h4>
                                <div>
                                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($vip['full_name']) ?></h6>
                                    <small class="text-muted"><?= $vip['stay_count'] ?> Stays logged</small>
                                </div>
                            </div>
                            <span class="badge bg-success rounded-pill px-3 py-2">
                                $<?= number_format($vip['total_spent'] ?: 0, 2) ?> Spent
                            </span>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($most_frequent_guests)): ?>
                        <div class="text-center py-4 text-muted">No guest activity recorded yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
