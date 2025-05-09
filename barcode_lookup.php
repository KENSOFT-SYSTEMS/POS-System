<?php
require 'database.php';
$code = $_GET['code'] ?? '';
$result = $conn->query("SELECT id FROM inventory WHERE barcode = '$code'");
$row = $result->fetch_assoc();
echo json_encode(['product_id' => $row['id'] ?? null]);
