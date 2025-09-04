<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../../public/admin_login.php");
    exit();
}

// Include database connection
$pdo = require_once __DIR__ . '/../../../config/db.php';

// Fetch all hotel bookings with user and hotel info
$stmt = $pdo->query("
    SELECT hb.id, u.fullName, b.id AS booking_id, h.name AS hotel_name,
           hb.checkin, hb.checkout, hb.rooms, hb.guests, hb.total_price, hb.created_at
    FROM hotel_bookings hb
    JOIN users u ON hb.user_id = u.id
    JOIN bookings b ON hb.booking_id = b.id
    JOIN hotels h ON hb.hotel_id = h.id
    ORDER BY hb.created_at DESC
");
$hotelBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch summary per hotel
$summaryStmt = $pdo->query("
    SELECT h.name AS hotel_name, SUM(hb.rooms) AS total_rooms, SUM(hb.total_price) AS total_revenue
    FROM hotel_bookings hb
    JOIN hotels h ON hb.hotel_id = h.id
    GROUP BY h.name
    ORDER BY total_revenue DESC
");
$hotelSummary = $summaryStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Hotel Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { margin:0; min-height:100vh; background:#f9fafb; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.sidebar { width:220px; background:#343a40; color:#fff; padding:20px 0; flex-shrink:0; height:100vh; }
.sidebar a { display:block; padding:12px 20px; color:#fff; text-decoration:none; margin-bottom:5px; }
.sidebar a:hover, .sidebar a.active { background-color:#495057; }
.main-content { flex:1; padding:20px; overflow-x:auto; }
.ads { width:200px; background:#e9ecef; padding:10px; flex-shrink:0; }

h3 { color:#0b5fff; margin-bottom:20px; }
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
        <h3>🏨 Hotel Bookings Management</h3>

        <!-- Summary Section -->
        <div class="summary-card">
            <h5>📊 Summary by Hotel</h5>
            <table class="table table-bordered summary-table">
                <thead>
                    <tr>
                        <th>Hotel Name</th>
                        <th>Total Rooms Booked</th>
                        <th>Total Revenue ($)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hotelSummary as $sum): ?>
                        <tr>
                            <td><?= htmlspecialchars($sum['hotel_name']) ?></td>
                            <td><?= $sum['total_rooms'] ?></td>
                            <td>$<?= number_format($sum['total_revenue'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Detailed Bookings Table -->
        <div class="summary-card">
            <h5>📃 All Hotel Bookings</h5>
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Booking ID</th>
                        <th>User</th>
                        <th>Hotel</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Rooms</th>
                        <th>Guests</th>
                        <th>Total Price ($)</th>
                        <th>Booked At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hotelBookings as $booking): ?>
                        <tr>
                            <td>#<?= $booking['booking_id'] ?></td>
                            <td><?= htmlspecialchars($booking['fullName']) ?></td>
                            <td><?= htmlspecialchars($booking['hotel_name']) ?></td>
                            <td><?= $booking['checkin'] ?></td>
                            <td><?= $booking['checkout'] ?></td>
                            <td><?= $booking['rooms'] ?></td>
                            <td><?= $booking['guests'] ?></td>
                            <td>$<?= number_format($booking['total_price'], 2) ?></td>
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
