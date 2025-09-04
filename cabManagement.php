<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../../public/admin_login.php");
    exit();
}

// Include database connection
$pdo = require_once __DIR__ . '/../../../config/db.php';

// Fetch detailed cab bookings
$stmt = $pdo->query("
    SELECT cb.id, u.fullName, b.id AS booking_id, c.car_name, cb.pickupLocation, cb.dropLocation, cb.pickupDate,
           cb.pickupTime, cb.dressCode, cb.cabFare, cb.estimatedDistance, cb.created_at
    FROM cab_bookings cb
    JOIN users u ON cb.user_id = u.id
    JOIN bookings b ON cb.booking_id = b.id
    JOIN cars c ON cb.car_id = c.id
    ORDER BY cb.created_at DESC
");
$cabBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch summary: total bookings & revenue per car
$summaryStmt = $pdo->query("
    SELECT c.car_name, COUNT(cb.id) AS total_bookings, SUM(cb.cabFare) AS total_revenue
    FROM cab_bookings cb
    JOIN cars c ON cb.car_id = c.id
    GROUP BY c.car_name
    ORDER BY total_revenue DESC
");
$cabSummary = $summaryStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Cab Management</title>
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
        <h3>🚖 Cab Bookings Management</h3>

        <!-- Summary Section -->
        <div class="summary-card">
            <h5>📌 Cab Booking Summary</h5>
            <table class="table table-bordered summary-table">
                <thead>
                    <tr>
                        <th>Car Name</th>
                        <th>Total Bookings</th>
                        <th>Total Revenue ($)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cabSummary as $sum): ?>
                        <tr>
                            <td><?= htmlspecialchars($sum['car_name']) ?></td>
                            <td><?= $sum['total_bookings'] ?></td>
                            <td>$<?= number_format($sum['total_revenue'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Detailed Bookings Table -->
        <div class="summary-card">
            <h5>📃 All Cab Bookings</h5>
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Booking ID</th>
                        <th>User</th>
                        <th>Car</th>
                        <th>Pickup</th>
                        <th>Drop</th>
                        <th>Pickup Date</th>
                        <th>Pickup Time</th>
                        <th>Dress Code</th>
                        <th>Fare ($)</th>
                        <th>Estimated Distance (km)</th>
                        <th>Booked At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cabBookings as $booking): ?>
                        <tr>
                            <td>#<?= $booking['booking_id'] ?></td>
                            <td><?= htmlspecialchars($booking['fullName']) ?></td>
                            <td><?= htmlspecialchars($booking['car_name']) ?></td>
                            <td><?= htmlspecialchars($booking['pickupLocation']) ?></td>
                            <td><?= htmlspecialchars($booking['dropLocation']) ?></td>
                            <td><?= $booking['pickupDate'] ?></td>
                            <td><?= $booking['pickupTime'] ?></td>
                            <td><?= htmlspecialchars($booking['dressCode']) ?></td>
                            <td>$<?= number_format($booking['cabFare'], 2) ?></td>
                            <td><?= $booking['estimatedDistance'] ?></td>
                            <td><?= $booking['created_at'] ?></td>
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
