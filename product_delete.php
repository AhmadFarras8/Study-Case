<?php
// admin/product_delete.php — delete a product (admin only, POST only)
require_once __DIR__ . '/../functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    $stmt = mysqli_prepare($conn, "SELECT image FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $p = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($p) {
        $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        if ($p['image'] && is_file(__DIR__ . '/../uploads/' . $p['image'])) {
            unlink(__DIR__ . '/../uploads/' . $p['image']);
        }
        flash('Product deleted. Past orders keep their item names and prices.');
    }
}
header("Location: index.php");
exit;
?>
