<?php
// login.php
require_once __DIR__ . '/functions.php';

if (is_logged_in()) {
    header("Location: " . (is_admin() ? "admin/index.php" : "index.php"));
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT id, name, password, role FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true); // prevent session fixation (cart is kept)
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin/index.php");
        } else {
            flash("Welcome back, " . $user['name'] . "! Your " . MEMBER_DISCOUNT_PERCENT . "% member discount is active.");
            header("Location: " . (!empty($_SESSION['cart']) ? "cart.php" : "index.php"));
        }
        exit;
    }
    // Deliberately vague: don't reveal which field was wrong
    $error = "Incorrect email or password.";
}

$title = 'Login';
include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white text-center">Login</div>
            <div class="card-body">
                <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="login" class="btn btn-dark">Login</button>
                    </div>
                </form>
                <p class="small text-center mt-3 mb-0">No account yet? <a href="register.php">Register</a></p>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
