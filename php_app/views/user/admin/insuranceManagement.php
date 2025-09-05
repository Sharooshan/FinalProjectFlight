<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../../public/admin_login.php");
    exit();
}

// Include database connection
$pdo = require_once __DIR__ . '/../../../config/db.php';

// Fetch all user insurance bookings with user info
$stmt = $pdo->query("
    SELECT ui.id, u.fullName, ui.plan_type, ui.start_date, ui.end_date, ui.price, ui.promo_code, ui.created_at
    FROM user_insurance ui
    JOIN users u ON ui.user_id = u.id
    ORDER BY ui.created_at DESC
");
$userInsurance = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch summary per promo code
$summaryStmt = $pdo->query("
    SELECT ip.code AS promo_code, ip.discount, ip.plan_type, ip.valid_until,
           COUNT(ui.id) AS total_users, SUM(ui.price) AS total_revenue
    FROM insurance_promos ip
    LEFT JOIN user_insurance ui ON ui.promo_code = ip.code
    GROUP BY ip.code
    ORDER BY total_revenue DESC
");
$promoSummary = $summaryStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Insurance Management</title>
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
        <h3>🛡️ Insurance Management</h3>

        <!-- Summary Section -->
        <div class="summary-card">
            <h5>📊 Promo Code Summary</h5>
            <table class="table table-bordered summary-table">
                <thead>
                    <tr>
                        <th>Promo Code</th>
                        <th>Discount (%)</th>
                        <th>Plan Type</th>
                        <th>Valid Until</th>
                        <th>Total Users</th>
                        <th>Total Revenue ($)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($promoSummary as $sum): ?>
                        <tr>
                            <td><?= htmlspecialchars($sum['promo_code']) ?></td>
                            <td><?= $sum['discount'] ?></td>
                            <td><?= htmlspecialchars($sum['plan_type']) ?></td>
                            <td><?= $sum['valid_until'] ?></td>
                            <td><?= $sum['total_users'] ?></td>
                            <td>$<?= number_format($sum['total_revenue'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Detailed User Insurance Table -->
        <div class="summary-card">
            <h5>📃 User Insurance Bookings</h5>
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Plan Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Price ($)</th>
                        <th>Promo Code</th>
                        <th>Booked At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($userInsurance as $ins): ?>
                        <tr>
                            <td><?= $ins['id'] ?></td>
                            <td><?= htmlspecialchars($ins['fullName']) ?></td>
                            <td><?= htmlspecialchars($ins['plan_type']) ?></td>
                            <td><?= $ins['start_date'] ?></td>
                            <td><?= $ins['end_date'] ?></td>
                            <td>$<?= number_format($ins['price'], 2) ?></td>
                            <td><?= htmlspecialchars($ins['promo_code']) ?></td>
                            <td><?= $ins['created_at'] ?></td>
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
