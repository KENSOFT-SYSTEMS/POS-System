<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'database.php';

// Summary queries
$products = $conn->query("SELECT COUNT(*) AS total FROM inventory")->fetch_assoc();
$categories = $conn->query("SELECT COUNT(DISTINCT category) AS total FROM inventory")->fetch_assoc();
$todaySales = $conn->query("SELECT SUM(quantity * i.price) AS total 
                            FROM sales s JOIN inventory i ON s.product_id = i.id 
                            WHERE DATE(s.sale_date) = CURDATE()")->fetch_assoc();
$weeklySales = $conn->query("SELECT SUM(quantity * i.price) AS total 
                             FROM sales s JOIN inventory i ON s.product_id = i.id 
                             WHERE s.sale_date >= CURDATE() - INTERVAL 7 DAY")->fetch_assoc();

// Monthly revenue for chart
$chartData = $conn->query("SELECT DATE_FORMAT(sale_date, '%Y-%m') AS month, SUM(quantity * i.price) AS revenue
                           FROM sales s
                           JOIN inventory i ON s.product_id = i.id
                           GROUP BY month
                           ORDER BY month ASC");

$months = [];
$revenues = [];
while ($row = $chartData->fetch_assoc()) {
    $months[] = $row['month'];
    $revenues[] = $row['revenue'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .btn:hover {
            transform: scale(1.03);
            transition: all 0.2s ease-in-out;
        }
        .tab-content {
            margin-top: 2rem;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
        <a class="navbar-brand" href="#">POS Dashboard</a>
        <div class="ms-auto text-white">
            Welcome, <?= htmlspecialchars($_SESSION['username']) ?> |
            <a href="logout.php" class="text-white ms-3">Logout</a>
        </div>
    </nav>

    <div class="container my-4">
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">📌 Overview</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="chart-tab" data-bs-toggle="tab" data-bs-target="#chart" type="button" role="tab">📈 Revenue Chart</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">🗂️ Inventory</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button" role="tab">💰 POS</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports" type="button" role="tab">📊 Reports</button>
            </li>
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content" id="dashboardTabsContent">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row g-4 mt-3">
                    <div class="col-md-3">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Products</h5>
                                <p class="display-6"><?= $products['total'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Categories</h5>
                                <p class="display-6"><?= $categories['total'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Sales Today</h5>
                                <p class="display-6">Ksh <?= number_format($todaySales['total'] ?? 0, 2) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Weekly Sales</h5>
                                <p class="display-6">Ksh <?= number_format($weeklySales['total'] ?? 0, 2) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Chart Tab -->
            <div class="tab-pane fade" id="chart" role="tabpanel">
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-primary text-white">Monthly Revenue</div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <!-- Inventory Tab -->
            <div class="tab-pane fade" id="inventory" role="tabpanel">
                <div class="text-center mt-4">
                    <a href="inventory.php" class="btn btn-primary btn-lg shadow-sm rounded-pill px-5">View Products</a>
                    <a href="add_product.php" class="btn btn-success btn-lg shadow-sm rounded-pill px-5 ms-3">Add Product</a>
                </div>
            </div>

            <!-- Sales Tab -->
            <div class="tab-pane fade" id="sales" role="tabpanel">
                <div class="text-center mt-4">
                    <a href="sales.php" class="btn btn-secondary btn-lg shadow-sm rounded-pill px-5">Go to POS</a>
                </div>
            </div>

            <!-- Reports Tab -->
            <div class="tab-pane fade" id="reports" role="tabpanel">
                <div class="text-center mt-4">
                    <a href="reports.php" class="btn btn-warning btn-lg shadow-sm rounded-pill px-5">Go to Reports</a>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [{
                    label: 'Revenue (Ksh)',
                    data: <?= json_encode($revenues) ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
