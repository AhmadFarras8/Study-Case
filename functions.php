<?php
// functions.php — session, DB connection and shared helpers (include on every page)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';

const MEMBER_DISCOUNT_PERCENT = 10;

// Escape output (XSS protection)
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Format a number as Rupiah
function rupiah($amount) {
    return 'Rp ' . number_format((int)$amount, 0, ',', '.');
}

function is_logged_in() { return isset($_SESSION['user_id']); }
function is_admin()     { return is_logged_in() && $_SESSION['role'] === 'admin'; }
function is_customer()  { return is_logged_in() && $_SESSION['role'] === 'customer'; }

// For pages inside /admin
function require_admin() {
    if (!is_logged_in()) {
        header("Location: ../login.php");
        exit;
    }
    if (!is_admin()) {
        http_response_code(403);
        die("403 - Access denied. This page is for admins only.");
    }
}

// Only registered, logged-in customers get the discount
function discount_percent() {
    return is_customer() ? MEMBER_DISCOUNT_PERCENT : 0;
}

// Returns [percent, discount amount, total]
function calc_totals($subtotal) {
    $percent  = discount_percent();
    $discount = (int)round($subtotal * $percent / 100);
    return [$percent, $discount, $subtotal - $discount];
}

function flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

// Read the cart (product_id => qty) and load current prices from the database
function get_cart($conn) {
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) {
        return ['items' => [], 'subtotal' => 0];
    }
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = mysqli_prepare($conn, "SELECT id, name, price, image FROM products WHERE id IN ($placeholders)");
    mysqli_stmt_bind_param($stmt, str_repeat('i', count($ids)), ...$ids);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $items = [];
    $subtotal = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $row['qty'] = $cart[$row['id']];
        $row['line_total'] = $row['price'] * $row['qty'];
        $subtotal += $row['line_total'];
        $items[] = $row;
    }
    // Remove products that no longer exist
    $found = array_column($items, 'id');
    foreach ($ids as $id) {
        if (!in_array($id, $found)) {
            unset($_SESSION['cart'][$id]);
        }
    }
    return ['items' => $items, 'subtotal' => $subtotal];
}

// Load the items of one order
function get_order_items($conn, $order_id) {
    $stmt = mysqli_prepare($conn, "SELECT product_name, unit_price, qty FROM order_items WHERE order_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
}
?>
