<?php
// admin/users.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireRole('Admin');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $role = $_POST['role'];
        
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $msg = "Error: Username already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $hashed, $role])) {
                $msg = "User added successfully.";
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)$_POST['user_id'];
        if ($id !== $_SESSION['user_id']) { // Check against self-deletion
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$id]);
            $msg = "User deleted successfully.";
        } else {
            $msg = "Error: You cannot delete your own active account.";
        }
    }
}

$users = $pdo->query("SELECT user_id, username, role, created_at FROM users ORDER BY role, created_at DESC")->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h2 class="page-title mb-0"><i class="fa-solid fa-user-shield me-2"></i>Manage System Personnel</h2>
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
</div>

<?php if ($msg): ?>
    <div class="alert <?= strpos($msg, 'Error') !== false ? 'alert-danger' : 'alert-success' ?>">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white pt-3 pb-0 border-0 fw-bold">Register Staff</div>
            <div class="card-body">
                <form action="users.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="Receptionist">Receptionist</option>
                            <option value="Admin">Admin</option>
                        </select>
                        <small class="text-muted mt-1 d-block">Customers should register via the public interface.</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Staff Account</button>
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
                                <th>ID</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Date Added</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td>#<?= $u['user_id'] ?></td>
                                <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                                <td>
                                    <?php 
                                        $rc = $u['role'] == 'Admin' ? 'danger' : ($u['role'] == 'Receptionist' ? 'primary' : 'secondary');
                                        echo "<span class='badge bg-{$rc}'>{$u['role']}</span>";
                                    ?>
                                </td>
                                <td class="text-muted small"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <?php if ($u['user_id'] !== $_SESSION['user_id']): ?>
                                        <form action="users.php" method="POST" onsubmit="return confirm('Remove this user?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-user-minus"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark">You</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
