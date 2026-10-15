<?php
// cart.php — cart (stored in $_SESSION) and checkout
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Add a product
    if ($action === 'add') {
        $pid = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, min(20, (int)($_POST['qty'] ?? 1)));

        $stmt = mysqli_prepare($conn, "SELECT id, name FROM products WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $pid);
        mysqli_stmt_execute($stmt);
        $p = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($p) {
            $_SESSION['cart'][$pid] = min(20, ($_SESSION['cart'][$pid] ?? 0) + $qty);
            flash($p['name'] . ' added to cart.');
        } else {
            flash('Product not found.', 'danger');
        }
        header("Location: index.php");
        exit;
    }

    // Update quantities / remove an item
    if ($action === 'update') {
        if (isset($_POST['remove_id'])) {
            unset($_SESSION['cart'][(int)$_POST['remove_id']]);
            flash('Item removed.');
        } else {
            foreach (($_POST['qty'] ?? []) as $pid => $q) {
                $pid = (int)$pid;
                $q = (int)$q;
                if (isset($_SESSION['cart'][$pid])) {
                    if ($q <= 0) {
                        unset($_SESSION['cart'][$pid]);
                    } else {
                        $_SESSION['cart'][$pid] = min(20, $q);
                    }
                }
            }
            flash('Cart updated.');
        }
        header("Location: cart.php");
        exit;
    }

    // Checkout: prices are ALWAYS re-read from the database, never trusted from the browser
    if ($action === 'checkout') {
        $cart = get_cart($conn);
        if (!$cart['items']) {
            flash('Your cart is empty.', 'danger');
            header("Location: cart.php");
            exit;
        }

        $name = is_logged_in() ? $_SESSION['name'] : trim($_POST['customer_name'] ?? '');
        if ($name === '' || strlen($name) > 100) {
            flash('Please enter your name (max 100 characters).', 'danger');
            header("Location: cart.php");
            exit;
        }

        $subtotal = $cart['subtotal'];
        [$percent, $discount, $total] = calc_totals($subtotal);
        $uid = is_logged_in() ? $_SESSION['user_id'] : null;

        mysqli_begin_transaction($conn);
        try {
            $stmt = mysqli_prepare($conn, "INSERT INTO orders (user_id, customer_name, subtotal, discount, total) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isiii", $uid, $name, $subtotal, $discount, $total);
            mysqli_stmt_execute($stmt);
            $order_id = mysqli_insert_id($conn);

            $stmt = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, product_name, unit_price, qty) VALUES (?, ?, ?, ?, ?)");
            foreach ($cart['items'] as $it) {
                $pid = $it['id'];
                $pname = $it['name'];
                $price = $it['price'];
                $qty = $it['qty'];
                mysqli_stmt_bind_param($stmt, "iisii", $order_id, $pid, $pname, $price, $qty);
                mysqli_stmt_execute($stmt);
            }
            mysqli_commit($conn);
        } catch (Throwable $ex) {
            mysqli_rollback($conn);
            flash('Could not place the order. Please try again.', 'danger');
            header("Location: cart.php");
            exit;
        }

        unset($_SESSION['cart']);
        $_SESSION['last_order_id'] = $order_id;
        header("Location: order_success.php?id=" . $order_id);
        exit;
    }
}

$cart = get_cart($conn);
[$percent, $discount, $total] = calc_totals($cart['subtotal']);

$title = 'Cart';
include 'header.php';
?>
<h3 class="mb-3">Your Cart</h3>

<?php if (!$cart['items']): ?>
    <div class="alert alert-secondary">Your cart is empty. <a href="index.php">Browse the menu</a>.</div>
<?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="cart.php">
                        <input type="hidden" name="action" value="update">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr><th>Product</th><th>Price</th><th style="width:100px;">Qty</th><th>Subtotal</th><th></th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart['items'] as $it): ?>
                                        <tr>
                                            <td><?= e($it['name']) ?></td>
                                            <td><?= rupiah($it['price']) ?></td>
                                            <td><input type="number" name="qty[<?= (int)$it['id'] ?>]" value="<?= (int)$it['qty'] ?>"
                                                       min="0" max="20" class="form-control form-control-sm"></td>
                                            <td><?= rupiah($it['line_total']) ?></td>
                                            <td><button type="submit" name="remove_id" value="<?= (int)$it['id'] ?>"
                                                        class="btn btn-outline-danger btn-sm">Remove</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-secondary btn-sm">Update quantities</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">Order Summary</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between"><span>Subtotal</span><span><?= rupiah($cart['subtotal']) ?></span></div>
                    <?php if ($percent > 0): ?>
                        <div class="d-flex justify-content-between text-success">
                            <span>Member discount (<?= $percent ?>%)</span><span>- <?= rupiah($discount) ?></span>
                        </div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span><?= rupiah($total) ?></span></div>

                    <?php if (!is_logged_in()): ?>
                        <div class="alert alert-info small mt-3 mb-0">
                            You are ordering as a guest (regular price).
                            <a href="login.php">Log in</a> or <a href="register.php">register</a> to save <?= MEMBER_DISCOUNT_PERCENT ?>%.
                            Your cart will be kept.
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="cart.php" class="mt-3">
                        <input type="hidden" name="action" value="checkout">
                        <?php if (!is_logged_in()): ?>
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Your name</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" maxlength="100" required>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">Ordering as <strong><?= e($_SESSION['name']) ?></strong></p>
                        <?php endif; ?>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">Place Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php include 'footer.php'; ?>
