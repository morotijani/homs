<?php
// login.php
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

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT user_id, username, password_hash, role FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Role-based redirection
            if ($user['role'] === 'Admin') {
                header("Location: /homs/admin/index.php");
            } elseif ($user['role'] === 'Receptionist') {
                header("Location: /homs/receptionist/index.php");
            } else {
                header("Location: /homs/customer/index.php");
            }
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header text-center bg-white border-0 pt-4">
                <h3 class="font-weight-bold text-primary"><i class="fa-solid fa-hotel me-2"></i>LuxeStay</h3>
                <p class="text-muted">Sign in to your account</p>
            </div>
            <div class="card-body px-5 pb-5">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['registered'])): ?>
                    <div class="alert alert-success">Registration successful! Please login.</div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-user"></i></span>
                            <input type="text" name="username" class="form-control" required placeholder="Enter username">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" required placeholder="Enter password">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-3">Sign In</button>
                    <div class="text-center">
                        <span class="text-muted">Don't have an account?</span> <a href="register.php" class="text-decoration-none">Register here</a>
                    </div>
                    
                    <div class="mt-4 text-center small text-muted">
                        <p class="mb-0"><strong>Demo Accounts (password: password123)</strong><br>
                        Admin: admin | Receptionist: receptionist<br>
                        Customer: johndoe</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
