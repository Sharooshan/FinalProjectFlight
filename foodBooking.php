<?php
session_start();
$pdo = include '../../config/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    die("Please login to order food.");
}
$user_id = $_SESSION['user_id'];

// Fetch latest flight booking for this user
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$user_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("No flight booking found. Please book a flight first.");
}

// Current time to filter foods
$currentTime = date("H:i:s");

// Fetch foods available now
$stmt = $pdo->prepare("SELECT * FROM foods WHERE available_from <= ? AND available_to >= ? ORDER BY category, food_name");
$stmt->execute([$currentTime, $currentTime]);
$foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $totalFoodPrice = 0;

    foreach ($_POST['quantity'] as $foodId => $qty) {
        $qty = max(0, (int)$qty);
        if ($qty === 0) continue;

        // Fetch food price
        $stmtFood = $pdo->prepare("SELECT price FROM foods WHERE id = ?");
        $stmtFood->execute([$foodId]);
        $food = $stmtFood->fetch(PDO::FETCH_ASSOC);

        $totalPrice = $food['price'] * $qty;
        $totalFoodPrice += $totalPrice;

        // Insert food booking
        $stmtInsert = $pdo->prepare("INSERT INTO food_bookings (user_id, booking_id, food_id, quantity, total_price) VALUES (?, ?, ?, ?, ?)");
        $stmtInsert->execute([$user_id, $booking['id'], $foodId, $qty, $totalPrice]);
    }

    // Update final bill
    $stmtBillCheck = $pdo->prepare("SELECT * FROM final_bills WHERE user_id = ? AND booking_id = ?");
    $stmtBillCheck->execute([$user_id, $booking['id']]);
    $bill = $stmtBillCheck->fetch(PDO::FETCH_ASSOC);

    $flightTotal = $booking['finalTotal'];
    $hotelTotal  = $bill['hotel_total'] ?? 0;
    $cabTotal    = $bill['cab_total'] ?? 0;
    $foodTotal   = ($bill['food_total'] ?? 0) + $totalFoodPrice;
    $grandTotal  = $flightTotal + $hotelTotal + $cabTotal + $foodTotal;

    if ($bill) {
        $stmtUpdate = $pdo->prepare("UPDATE final_bills SET food_total = ?, grand_total = ? WHERE id = ?");
        $stmtUpdate->execute([$foodTotal, $grandTotal, $bill['id']]);
    } else {
        $stmtInsert = $pdo->prepare("INSERT INTO final_bills (user_id, booking_id, flight_total, hotel_total, cab_total, food_total, grand_total) VALUES (?, ?, ?, 0, 0, ?, ?)");
        $stmtInsert->execute([$user_id, $booking['id'], $flightTotal, $foodTotal, $grandTotal]);
    }

    echo "<div class='alert alert-success mt-3'>
            ✅ Food ordered successfully! <br>
            💰 Total Food: $$totalFoodPrice <br>
            🧾 New Grand Total: $$grandTotal
          </div>";
    header("Refresh: 2; url=finalBill.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Food Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .food-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 12px;
        }
        .food-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="container-fluid p-4" style="flex:1;">
        <h2 class="mb-4">Order Food for Your Flight</h2>

        <?php if ($foods): ?>
            <form method="POST">
                <div class="row">
                    <?php foreach ($foods as $food): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card food-card">
                                <img src="<?= htmlspecialchars($food['photo_url'] ?? 'default-food.jpg') ?>" class="card-img-top" alt="Food Photo">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($food['food_name']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($food['description']) ?></p>
                                    <p><strong>$<?= $food['price'] ?></strong></p>
                                    <input type="number" name="quantity[<?= $food['id'] ?>]" min="0" value="0" class="form-control mb-2">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-primary w-100">Order Food</button>
            </form>
        <?php else: ?>
            <p>No food available at this time. Please check back later.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
