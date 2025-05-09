<?php
session_start();
require 'database.php';

// Fetch distinct categories from existing inventory
$categoryResult = $conn->query("SELECT DISTINCT category FROM inventory WHERE category IS NOT NULL AND category != ''");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['product_name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category = trim($_POST['category']);  // Now directly passed as a string

    // Insert product into inventory
    $stmt = $conn->prepare("INSERT INTO inventory (product_name, price, stock, category) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdis", $name, $price, $stock, $category);
    $stmt->execute();

    header("Location: inventory.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-4">
        <span class="navbar-brand">Add New Product</span>
        <div class="ms-auto">
            <a href="inventory.php" class="btn btn-outline-light btn-sm">Back to Inventory</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="card shadow-sm mx-auto" style="max-width: 500px;">
            <div class="card-body">
                <h5 class="card-title mb-4">New Product Details</h5>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="product_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (Ksh)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial Stock</label>
                        <input type="number" name="stock" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="">-- Select Existing Category --</option>
                            <?php while ($cat = $categoryResult->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($cat['category']) ?>">
                                    <?= htmlspecialchars($cat['category']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Add Product</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
