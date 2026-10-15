<?php
// admin/orders.php — all orders (admin only)
require_once __DIR__ . '/../functions.php';
require_admin();

$orders = mysqli_fetch_all(
    mysqli_query($conn, "SELECT id, user_id, customer_name, subtotal, discount, total, created_at FROM orders ORDER BY id DESC"),
    MYSQLI_ASSOC
);

$base = '../';
$title = 'Orders';
include '../header.php';
?>
<h3 class="mb-3">Orders</h3>
<?php if (!$orders): ?>
    <div class="alert alert-secondary">No orders yet.</div>
<?php endif; ?>
<?php foreach ($orders as $o): ?>
    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                Order #<?= (int)$o['id'] ?> &mdash; <?= e($o['customer_name']) ?>
                <?php if ($o['user_id']): ?><span class="badge bg-success">member</span><?php else: ?><span class="badge bg-secondary">guest</span><?php endif; ?>
            </span>
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
<?php include '../footer.php'; ?>
