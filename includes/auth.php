<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Require a specific role to access a page.
 * If user lacks the role, they are redirected.
 *
 * @param string|array $allowed_roles
 */
function requireRole($allowed_roles) {
    if (!isLoggedIn()) {
        header("Location: /homs/login.php");
        exit();
    }

    $current_role = $_SESSION['role'];
    if (is_array($allowed_roles)) {
        if (!in_array($current_role, $allowed_roles)) {
            header("Location: /homs/index.php?error=unauthorized");
            exit();
        }
    } else {
        if ($current_role !== $allowed_roles) {
            header("Location: /homs/index.php?error=unauthorized");
            exit();
        }
    }
}
?>
