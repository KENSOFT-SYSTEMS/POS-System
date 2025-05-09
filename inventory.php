<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'database.php';

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM inventory WHERE id = $id");
    header("Location: inventory.php");
    exit();
}

// Pagination setup
$perPage = 10;  // Number of items to display per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Search and category filter logic
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$query = "SELECT * FROM inventory";
$whereClauses = [];

if (!empty($search)) {
    $searchTerm = $conn->real_escape_string($search);
    $whereClauses[] = "product_name LIKE '%$searchTerm%'";
}

if (!empty($category_filter)) {
    $categoryFilterTerm = $conn->real_escape_string($category_filter);
    $whereClauses[] = "category LIKE '%$categoryFilterTerm%'";
}

if (!empty($whereClauses)) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
}

// Pagination query
$query .= " LIMIT $offset, $perPage"; 
$result = $conn->query($query);

// Fetch all distinct categories for the filter dropdown
$categories_result = $conn->query("SELECT DISTINCT category FROM inventory");

// Get the total number of rows for pagination
$totalQuery = "SELECT COUNT(*) AS total FROM inventory";
if (!empty($whereClauses)) {
    $totalQuery .= " WHERE " . implode(" AND ", $whereClauses);
}
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalItems = $totalRow['total'];
$totalPages = ceil($totalItems / $perPage);  // Total number of pages

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory - POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .action-buttons a { margin-right: 5px; }
        .pagination a { margin: 0 3px; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-4">
        <span class="navbar-brand">Product Inventory</span>
        <div class="ms-auto">
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Back to Dashboard</a>
            <a href="add_product.php" class="btn btn-success btn-sm">Add Product</a>
        </div>
    </nav>

    <div class="container mt-4">
        <form method="get" class="mb-3 d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Search product..." value="<?= htmlspecialchars($search) ?>">
            
            <select name="category" class="form-select me-2" aria-label="Category filter">
                <option value="">All Categories</option>
                <?php while ($category = $categories_result->fetch_assoc()): ?>
                    <option value="<?= $category['category'] ?>" <?= ($category['category'] == $category_filter) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['category']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button class="btn btn-outline-primary">Search</button>
        </form>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">Product List</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price (Ksh)</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                                        <td><?= htmlspecialchars($row['category']) ?></td>
                                        <td><?= number_format($row['price'], 2) ?></td>
                                        <td><?= $row['stock'] ?></td>
                                        <td class="action-buttons">
                                            <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="inventory.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">No products found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination links -->
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="inventory.php?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>&category=<?= htmlspecialchars($category_filter) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</body>
</html>
