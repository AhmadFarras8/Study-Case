<?php
// order_success.php — order confirmation
require_once __DIR__ . '/functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Only the person who just placed it, the owner, or an admin may view the order
$allowed = $order && (
    ($_SESSION['last_order_id'] ?? 0) == $id ||
    (is_logged_in() && $order['user_id'] == $_SESSION['user_id']) ||
    is_admin()
);
if (!$allowed) {
    http_response_code(404);
    die("Order not found.");
}

$items = get_order_items($conn, $id);
$title = 'Order Placed';
include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">Thank you, <?= e($order['customer_name']) ?>! Order #<?= (int)$order['id'] ?> received.</div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td><?= e($it['product_name']) ?></td>
                                <td><?= rupiah($it['unit_price']) ?></td>
                                <td><?= (int)$it['qty'] ?></td>
                                <td><?= rupiah($it['unit_price'] * $it['qty']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="d-flex justify-content-between"><span>Subtotal</span><span><?= rupiah($order['subtotal']) ?></span></div>
                <?php if ($order['discount'] > 0): ?>
                    <div class="d-flex justify-content-between text-success"><span>Member discount</span><span>- <?= rupiah($order['discount']) ?></span></div>
                <?php endif; ?>
                <div class="d-flex justify-content-between fw-bold fs-5 mt-2"><span>Total</span><span><?= rupiah($order['total']) ?></span></div>
                <a href="index.php" class="btn btn-dark mt-4">Back to menu</a>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
