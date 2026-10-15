<?php
// my_orders.php — order history of the logged-in customer
require_once __DIR__ . '/functions.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT id, subtotal, discount, total, created_at FROM orders WHERE user_id = ? ORDER BY id DESC");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$orders = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

$title = 'My Orders';
include 'header.php';
?>
<h3 class="mb-3">My Orders</h3>
<?php if (!$orders): ?>
    <div class="alert alert-secondary">You have no orders yet. <a href="index.php">Browse the menu</a>.</div>
<?php endif; ?>
<?php foreach ($orders as $o): ?>
    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between">
            <span>Order #<?= (int)$o['id'] ?></span>
            <span class="text-muted small"><?= e($o['created_at']) ?></span>
        </div>
        <div class="card-body">
            <ul class="mb-2">
                <?php foreach (get_order_items($conn, $o['id']) as $it): ?>
                    <li><?= e($it['product_name']) ?> &times; <?= (int)$it['qty'] ?> (<?= rupiah($it['unit_price'] * $it['qty']) ?>)</li>
                <?php endforeach; ?>
            </ul>
            <div class="small text-muted">Subtotal <?= rupiah($o['subtotal']) ?>
                <?php if ($o['discount'] > 0): ?> &middot; Discount - <?= rupiah($o['discount']) ?><?php endif; ?></div>
            <div class="fw-bold">Total <?= rupiah($o['total']) ?></div>
        </div>
    </div>
<?php endforeach; ?>
<?php include 'footer.php'; ?>
