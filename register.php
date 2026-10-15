<?php
// register.php — customer registration (role is always 'customer')
require_once __DIR__ . '/functions.php';

if (is_logged_in()) {
    header("Location: index.php");
    exit;
}

$error = "";
$name = "";
$email = "";

if (isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($name === '' || strlen($name) > 100) {
        $error = "Please enter your name (max 100 characters).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'customer';
        $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hash, $role);
        try {
            mysqli_stmt_execute($stmt);
            // Log the new customer in straight away; the cart stays in the session
            session_regenerate_id(true);
            $_SESSION['user_id'] = mysqli_insert_id($conn);
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;
            flash("Welcome, $name! Your " . MEMBER_DISCOUNT_PERCENT . "% member discount is now active.");
            header("Location: index.php");
            exit;
        } catch (mysqli_sql_exception $ex) {
            // 1062 = duplicate value in a UNIQUE column (email)
            $error = "That email is already registered.";
        }
    }
}

$title = 'Register';
include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-warning">Create an account (get <?= MEMBER_DISCOUNT_PERCENT ?>% off)</div>
            <div class="card-body">
                <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= e($name) ?>" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                    </div>
                    <div class="mb-4">
                        <label for="confirm" class="form-label">Confirm password</label>
                        <input type="password" class="form-control" id="confirm" name="confirm" minlength="6" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="register" class="btn btn-dark">Register</button>
                    </div>
                </form>
                <p class="small text-center mt-3 mb-0">Already have an account? <a href="login.php">Log in</a></p>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
