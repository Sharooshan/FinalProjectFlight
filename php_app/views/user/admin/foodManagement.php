<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../../public/admin_login.php");
    exit();
}

// Include database connection
$pdo = require_once __DIR__ . '/../../../config/db.php';

// Fetch detailed food orders
$stmt = $pdo->query("
    SELECT fb.id, u.fullName, b.id AS booking_id, f.food_name AS food_item, fb.quantity, fb.total_price, fb.created_at
    FROM food_bookings fb
    JOIN users u ON fb.user_id = u.id
    JOIN bookings b ON fb.booking_id = b.id
    JOIN foods f ON fb.food_id = f.id
    ORDER BY fb.created_at DESC
");
$foodOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch summary: total quantities & sales by food item
$summaryStmt = $pdo->query("
    SELECT f.food_name, SUM(fb.quantity) AS total_quantity, SUM(fb.total_price) AS total_sales
    FROM food_bookings fb
    JOIN foods f ON fb.food_id = f.id
    GROUP BY f.food_name
    ORDER BY total_sales DESC
");
$foodSummary = $summaryStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Food Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { margin:0; min-height:100vh; background:#f9fafb; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.sidebar { width:220px; background:#343a40; color:#fff; padding:20px 0; flex-shrink:0; height:100vh; }
.sidebar a { display:block; padding:12px 20px; color:#fff; text-decoration:none; margin-bottom:5px; }
.sidebar a:hover, .sidebar a.active { background-color:#495057; }
.main-content { flex:1; padding:20px; overflow-x:auto; }
.ads { width:200px; background:#e9ecef; padding:10px; flex-shrink:0; }

h3 { color:#1f2937; margin-bottom:20px; }
.summary-card {
    background:#ffffff;
    border-radius:12px;
    padding:20px;
    margin-bottom:20px;
    box-shadow:0px 4px 8px rgba(0,0,0,0.1);
}
.summary-card h5 { font-size:1.2rem; margin-bottom:15px; color:#0b5fff; }
.summary-table th { background:#0b5fff; color:#fff; }
</style>
</head>
<body>
<div class="d-flex">
    <!-- Left Sidebar -->
    <?php include __DIR__ . '/sidebar_admin.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <h3>🍔 Food Orders Management</h3>

        <div class="mb-3">
            <a href="generate_food_report.php" class="btn btn-success">📊 Generate Food Report</a>
        </div>

        <!-- Summary Section -->
        <div class="summary-card">
            <h5>📌 Order Summary</h5>
            <table class="table table-bordered summary-table">
                <thead>
                    <tr>
                        <th>Food Item</th>
                        <th>Total Quantity</th>
                        <th>Total Sales ($)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($foodSummary as $sum): ?>
                        <tr>
                            <td><?= htmlspecialchars($sum['food_name']) ?></td>
                            <td><?= $sum['total_quantity'] ?></td>
                            <td>$<?= number_format($sum['total_sales'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Detailed Orders Table -->
        <div class="summary-card">
            <h5>📃 All Food Orders</h5>
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Booking</th>
                        <th>Food Item</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Ordered At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($foodOrders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['fullName']) ?></td>
                            <td>#<?= $order['booking_id'] ?></td>
                            <td><?= htmlspecialchars($order['food_item']) ?></td>
                            <td><?= $order['quantity'] ?></td>
                            <td>$<?= number_format($order['total_price'], 2) ?></td>
                            <td><?= $order['created_at'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Sidebar / Ads -->
    <div class="ads">
        <h5>Sponsored</h5>
        <img src="https://via.placeholder.com/150" class="img-fluid mb-2" alt="Ad">
        <img src="https://via.placeholder.com/150" class="img-fluid" alt="Ad">
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
