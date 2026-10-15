<?php
// admin/product_form.php — add or edit a product, including its image (admin only)
require_once __DIR__ . '/../functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$product = ['name' => '', 'description' => '', 'price' => '', 'image' => null];

if ($id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$product) {
        die("Product not found.");
    }
}

$errors = [];

if (isset($_POST['save'])) {
    $product['name'] = trim($_POST['name'] ?? '');
    $product['description'] = trim($_POST['description'] ?? '');
    $product['price'] = trim($_POST['price'] ?? '');

    if ($product['name'] === '' || strlen($product['name']) > 100) {
        $errors[] = "Name is required (max 100 characters).";
    }
    if (strlen($product['description']) > 255) {
        $errors[] = "Description is too long (max 255 characters).";
    }
    $price = filter_var($product['price'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10000000]]);
    if ($price === false) {
        $errors[] = "Price must be a whole number in Rupiah (1 - 10,000,000).";
    }

    // Image upload (optional)
    $newImage = null;
    $file = $_FILES['image'] ?? null;
    if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Image upload failed. Try a smaller file.";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = "Image must be 2 MB or smaller.";
        } else {
            $info = @getimagesize($file['tmp_name']); // checks the real file content
            if (!$info || !isset($allowed[$info['mime']])) {
                $errors[] = "Image must be a JPG, PNG, WEBP or GIF file.";
            } else {
                $newImage = bin2hex(random_bytes(8)) . '.' . $allowed[$info['mime']]; // random safe name
            }
        }
    }

    if (!$errors) {
        $oldImage = $product['image'];
        if ($newImage) {
            move_uploaded_file($file['tmp_name'], __DIR__ . '/../uploads/' . $newImage);
        }
        $imageToSave = $newImage ?: $oldImage;

        if ($id) {
            $stmt = mysqli_prepare($conn, "UPDATE products SET name = ?, description = ?, price = ?, image = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssisi", $product['name'], $product['description'], $price, $imageToSave, $id);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssis", $product['name'], $product['description'], $price, $imageToSave);
        }
        mysqli_stmt_execute($stmt);

        // Remove the replaced image file
        if ($newImage && $oldImage && is_file(__DIR__ . '/../uploads/' . $oldImage)) {
            unlink(__DIR__ . '/../uploads/' . $oldImage);
        }
        flash($id ? 'Product updated.' : 'Product added.');
        header("Location: index.php");
        exit;
    }
}

$base = '../';
$title = $id ? 'Edit Product' : 'Add Product';
include '../header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header <?= $id ? 'bg-warning' : 'bg-success text-white' ?>"><?= $id ? 'Edit Product' : 'Add New Product' ?></div>
            <div class="card-body">
                <?php foreach ($errors as $err): ?>
                    <div class="alert alert-danger py-2"><?= e($err) ?></div>
                <?php endforeach; ?>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= e($product['name']) ?>" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2" maxlength="255"><?= e($product['description']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price (Rp)</label>
                        <input type="number" class="form-control" id="price" name="price" value="<?= e($product['price']) ?>" min="1" max="10000000" required>
                    </div>
                    <div class="mb-4">
                        <label for="image" class="form-label">Image (JPG, PNG, WEBP or GIF, max 2 MB)</label>
                        <?php if ($product['image']): ?>
                            <div class="mb-2">
                                <img src="../uploads/<?= e($product['image']) ?>" alt="" style="height:100px;" class="rounded">
                                <div class="small text-muted">Choose a new file only if you want to replace it.</div>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="save" class="btn btn-dark">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../footer.php'; ?>
