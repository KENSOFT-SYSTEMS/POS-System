<?php
require 'database.php';

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $result = $conn->query("SELECT price FROM inventory WHERE id = $product_id");

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        echo json_encode(['price' => $product['price']]);
    } else {
        echo json_encode(['price' => 0]);
    }
} else {
    echo json_encode(['price' => 0]);
}
