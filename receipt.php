<?php
session_start();
require 'database.php';

$id = $_GET['id'] ?? 0;
$sale = $conn->query("SELECT s.*, i.product_name, i.price 
                      FROM sales s 
                      JOIN inventory i ON s.product_id = i.id 
                      WHERE s.id = $id")->fetch_assoc();

if (!$sale) {
    echo "Receipt not found.";
    exit();
}

$total = $sale['quantity'] * $sale['price'];
$paid = $sale['amount_paid'] ?? 0;
$change = $paid - $total;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
        body { font-family: monospace; width: 300px; margin: 20px auto; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
    </style>
</head>
<body onload="window.print()">
    <div class="center">
        <h3>Mini POS Shop</h3>
        <p>Receipt #: <?= $sale['id'] ?><br>
        Date: <?= $sale['sale_date'] ?></p>
        <hr>
        <p>
            Product: <?= $sale['product_name'] ?><br>
            Unit Price: Ksh <?= number_format($sale['price'], 2) ?><br>
            Quantity: <?= $sale['quantity'] ?><br>
            ------------------------------<br>
            <span class="bold">Total: Ksh <?= number_format($total, 2) ?></span><br>
            Paid: Ksh <?= number_format($paid, 2) ?><br>
            Change: Ksh <?= number_format($change, 2) ?>
        </p>
        <hr>
        <p>Thank you for shopping!</p>
    </div>
</body>
</html>
