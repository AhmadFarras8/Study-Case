<?php
// header.php — opens the page and shows the navbar. Set $title and $base before including.
$base = $base ?? '';
$title = $title ?? 'Menu';
$cartCount = array_sum($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= $base ?>index.php">Coffee Shop</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= $base ?>index.php">Menu</a></li>
                <?php if (is_customer()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base ?>my_orders.php">My Orders</a></li>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base ?>admin/index.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $base ?>admin/orders.php">Orders</a></li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= $base ?>cart.php" class="btn btn-outline-light btn-sm">
                    Cart <span class="badge bg-warning text-dark"><?= $cartCount ?></span>
                </a>
                <?php if (is_logged_in()): ?>
                    <span class="text-white small">
                        <?= e($_SESSION['name']) ?>
                        <span class="badge bg-secondary"><?= e($_SESSION['role']) ?></span>
                    </span>
                    <a href="<?= $base ?>logout.php" class="btn btn-danger btn-sm">Logout</a>
                <?php else: ?>
                    <a href="<?= $base ?>login.php" class="btn btn-light btn-sm">Login</a>
                    <a href="<?= $base ?>register.php" class="btn btn-warning btn-sm">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<div class="container mt-4">
<?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?= e($_SESSION['flash']['type']) ?> alert-dismissible fade show">
        <?= e($_SESSION['flash']['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
