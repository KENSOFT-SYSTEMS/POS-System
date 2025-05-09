<?php
session_start();
require 'database.php';

// Fetch the sales report
$report = $conn->query("
    SELECT i.product_name, SUM(s.quantity) AS total_sold
    FROM sales s
    JOIN inventory i ON s.product_id = i.id
    GROUP BY s.product_id
");

// Check if there was an error in the query
if (!$report) {
    die("Error fetching report: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-4">
    <a href="dashboard.php" class="btn btn-outline-primary mb-3">Back to Dashboard</a>

    <h2 class="mb-4">Sales Report</h2>

    <!-- Report Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Product</th>
                        <th>Total Sold</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($report->num_rows > 0): ?>
                        <?php while ($row = $report->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['product_name']) ?></td>
                                <td><?= htmlspecialchars($row['total_sold']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="text-center">No sales data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
