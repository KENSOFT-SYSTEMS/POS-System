<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'database.php';

if (!isset($_GET['id'])) {
    header("Location: inventory.php");
    exit();
}

$id = intval($_GET['id']);
$product = $conn->query("SELECT * FROM inventory WHERE id = $id")->fetch_assoc();

if (!$product) {
    echo "Product not found.";
    exit();
}

// Fetch existing categories for dropdown
$categories = $conn->query("SELECT DISTINCT category FROM inventory WHERE category IS NOT NULL AND category != ''");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];

    $stmt = $conn->prepare("UPDATE inventory SET product_name = ?, price = ?, stock = ?, category = ? WHERE id = ?");
    $stmt->bind_param("sdiss", $name, $price, $stock, $category, $id);
    $stmt->execute();

    header("Location: inventory.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">Edit Product</div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="product_name" class="form-control" value="<?= htmlspecialchars($product['product_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (Ksh)</label>
                        <input type="number" name="price" step="0.01" class="form-control" value="<?= $product['price'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($cat['category']) ?>"
                                    <?= $cat['category'] === $product['category'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['category']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Update Product</button>
                    <a href="inventory.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
