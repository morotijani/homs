<?php
// register.php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    if ($username && $password && $full_name && $email && $phone) {
        try {
            $pdo->beginTransaction();

            // 1. Check if username or email already exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) throw new Exception("Username already exists.");

            $stmt = $pdo->prepare("SELECT guest_id FROM guests WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) throw new Exception("Email already registered.");

            // 2. Insert User
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'Customer')");
            $stmt->execute([$username, $hashed]);
            $user_id = $pdo->lastInsertId();

            // 3. Insert Guest Data
            $stmt = $pdo->prepare("INSERT INTO guests (user_id, full_name, email, phone) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $full_name, $email, $phone]);

            $pdo->commit();
            header("Location: login.php?registered=1");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="row justify-content-center my-4">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-white border-0 pt-4 text-center">
                <h3 class="font-weight-bold text-primary">Create Customer Account</h3>
                <p class="text-muted">Join LuxeStay today</p>
            </div>
            <div class="card-body px-5 pb-5">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <h5 class="mb-3 text-muted border-bottom pb-2">Account Details</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                    </div>

                    <h5 class="mb-3 mt-4 text-muted border-bottom pb-2">Personal Information</h5>
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number *</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">Register Account</button>
                    <div class="text-center">
                        <span class="text-muted">Already have an account?</span> <a href="login.php" class="text-decoration-none">Sign in</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
