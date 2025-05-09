<?php 
session_start();
require 'database.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$total_price = 0;
$change = 0;
$product_id = 0;
$quantity = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $amount_paid = floatval($_POST['amount_paid']);

    $stockCheck = $conn->query("SELECT stock, price FROM inventory WHERE id = $product_id")->fetch_assoc();
    if ($stockCheck && $stockCheck['stock'] >= $quantity) {
        $total_price = $stockCheck['price'] * $quantity;
        $change = $amount_paid - $total_price;

        $conn->query("UPDATE inventory SET stock = stock - $quantity WHERE id = $product_id");

        $stmt = $conn->prepare("INSERT INTO sales (product_id, quantity, total_price, amount_paid, changer, sale_date) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiddd", $product_id, $quantity, $total_price, $amount_paid, $change);
        $stmt->execute();

        header("Location: sales.php?success=1");
        exit();
    } else {
        $error = "Not enough stock available.";
    }
}

$categories = $conn->query("SELECT DISTINCT category FROM inventory");
$sales = $conn->query("SELECT s.id, i.product_name, s.quantity, s.total_price, s.amount_paid, s.changer, s.sale_date 
                       FROM sales s 
                       JOIN inventory i ON s.product_id = i.id 
                       ORDER BY s.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-4">
    <a href="dashboard.php" class="btn btn-outline-primary mb-3">Back to Dashboard</a>
    
    <h2>Record Sale</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif (isset($_GET['success'])): ?>
        <div class="alert alert-success">Sale recorded successfully.</div>
    <?php endif; ?>

    <form method="post" class="card p-3 shadow-sm bg-white mb-4">
        <ul class="nav nav-tabs" id="categoryTabs" role="tablist">
            <?php 
            $i = 0;
            $categories->data_seek(0);
            while ($cat = $categories->fetch_assoc()): 
                $active = $i === 0 ? 'active' : '';
            ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $active ?>" id="tab-<?= $i ?>" data-bs-toggle="tab" data-bs-target="#content-<?= $i ?>" type="button" role="tab">
                        <?= htmlspecialchars($cat['category']) ?>
                    </button>
                </li>
            <?php $i++; endwhile; ?>
        </ul>

        <div class="tab-content mt-3">
            <?php 
            $i = 0;
            $categories->data_seek(0);
            while ($cat = $categories->fetch_assoc()): 
                $category_name = $cat['category'];
                $active = $i === 0 ? 'show active' : '';
                $products = $conn->query("SELECT * FROM inventory WHERE category = '$category_name'");
            ?>
                <div class="tab-pane fade <?= $active ?>" id="content-<?= $i ?>" role="tabpanel">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <select name="product_id" class="form-select product-select" required onchange="calculateTotal()">
                            <?php while ($p = $products->fetch_assoc()): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['product_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            <?php $i++; endwhile; ?>
        </div>

        <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" value="1" min="1" onchange="calculateTotal()" required>
        </div>

        <div class="mb-3">
            <span id="total_price" class="fw-bold">Total: 0.00</span>
        </div>

        <div class="mb-3">
            <label class="form-label">Amount Paid</label>
            <input type="number" name="amount_paid" class="form-control" required min="0" step="0.01">
        </div>

        <button type="submit" class="btn btn-success">Sell</button>
    </form>

    <h3>Sales Log</h3>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Total Price</th>
                <th>Amount Paid</th>
                <th>Change</th>
                <th>Date</th>
                <th>Receipt</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($s = $sales->fetch_assoc()): ?>
            <tr>
                <td><?= $s['id'] ?></td>
                <td><?= htmlspecialchars($s['product_name']) ?></td>
                <td><?= $s['quantity'] ?></td>
                <td><?= number_format($s['total_price'], 2) ?></td>
                <td><?= number_format($s['amount_paid'], 2) ?></td>
                <td><?= number_format($s['changer'], 2) ?></td>
                <td><?= $s['sale_date'] ?></td>
                <td><a href="receipt.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank">Print</a></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function calculateTotal() {
    const activeTab = document.querySelector('.tab-pane.show.active');
    if (!activeTab) return;

    const select = activeTab.querySelector('select[name="product_id"]');
    const quantity = parseInt(document.querySelector('input[name="quantity"]').value) || 1;

    if (!select || !select.value) return;

    fetch('get_product_price.php?id=' + select.value)
        .then(res => res.json())
        .then(data => {
            const price = parseFloat(data.price);
            const total = price * quantity;
            document.getElementById('total_price').innerText = 'Total: ' + total.toFixed(2);
        });
}

document.addEventListener('DOMContentLoaded', () => {
    calculateTotal();

    document.querySelector('input[name="quantity"]').addEventListener('input', calculateTotal);

    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', () => {
            calculateTotal();
        });
    });
});
</script>
</body>
</html>
