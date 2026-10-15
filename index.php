<?php
// admin/index.php — product list (admin only)
require_once __DIR__ . '/../functions.php';
require_admin();

$products = mysqli_query($conn, "SELECT id, name, description, price, image FROM products ORDER BY id DESC");

$base = '../';
$title = 'Manage Products';
include '../header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Manage Products</h3>
    <a href="product_form.php" class="btn btn-success">+ Add Product</a>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr><th>No</th><th>Image</th><th>Name</th><th>Description</th><th>Price</th><th class="text-center">Actions</th></tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($p = mysqli_fetch_assoc($products)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <?php if ($p['image']): ?>
                                    <img src="../uploads/<?= e($p['image']) ?>" alt="" style="width:60px;height:60px;object-fit:cover;" class="rounded">
                                <?php else: ?>
                                    <span class="text-muted small">no image</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($p['name']) ?></td>
                            <td class="small"><?= e($p['description']) ?></td>
                            <td><?= rupiah($p['price']) ?></td>
                            <td class="text-center">
                                <a href="product_form.php?id=<?= (int)$p['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <form method="POST" action="product_delete.php" class="d-inline"
                                      onsubmit="return confirm('Delete this product?')">
                                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../footer.php'; ?>
