<?php
session_start();
$pdo = include '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Please login to view your final bill.");
}
$user_id = $_SESSION['user_id'];

// Fetch latest final bill
$sql = "SELECT fb.*, b.user_name, b.startingAirport, b.destinationAirport, b.seatsBooked, b.airline, b.arrivalTime 
        FROM final_bills fb
        JOIN bookings b ON fb.booking_id = b.id
        WHERE fb.user_id = ?
        ORDER BY fb.created_at DESC LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$bill = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bill) {
    die("No final bill found. Please book services first.");
}

// Set default insurance if not purchased yet
if ($bill['insurance_total'] == 0) {
    $defaultInsurance = 50; // default insurance price
    $bill['insurance_total'] = $defaultInsurance;
    $bill['grand_total'] = $bill['flight_total'] + $bill['cab_total'] + $bill['hotel_total'] + $bill['food_total'] + $bill['insurance_total'];

    // Update DB
    $stmtUpdate = $pdo->prepare("UPDATE final_bills SET insurance_total = ?, grand_total = ? WHERE id = ?");
    $stmtUpdate->execute([$bill['insurance_total'], $bill['grand_total'], $bill['id']]);
}

// Handle promo code
$promoMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['insurance_promo'])) {
    $enteredPromo = strtoupper(trim($_POST['insurance_promo']));
    
    $stmtPromo = $pdo->prepare("SELECT discount FROM insurance_promos WHERE code = ? AND valid_until >= CURDATE()");
    $stmtPromo->execute([$enteredPromo]);
    $promo = $stmtPromo->fetch(PDO::FETCH_ASSOC);
    
    if ($promo) {
        $discount = $promo['discount']; // percentage
        $bill['insurance_total'] = $bill['insurance_total'] * (1 - $discount / 100);
        $bill['grand_total'] = $bill['flight_total'] + $bill['cab_total'] + $bill['hotel_total'] + $bill['food_total'] + $bill['insurance_total'];

        // Update DB
        $stmtUpdate = $pdo->prepare("UPDATE final_bills SET insurance_total = ?, grand_total = ? WHERE id = ?");
        $stmtUpdate->execute([$bill['insurance_total'], $bill['grand_total'], $bill['id']]);

        $promoMessage = "✅ Promo applied! You got $discount% off on insurance.";
    } else {
        $promoMessage = "❌ Invalid or expired promo code.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Final Bill</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Final Bill Summary</h2>

    <?php if ($promoMessage): ?>
        <div class="alert alert-info"><?= $promoMessage ?></div>
    <?php endif; ?>

    <!-- Promo Code Form -->
    <form method="POST" class="mb-3">
        <div class="input-group mb-3">
            <input type="text" name="insurance_promo" class="form-control" placeholder="Enter Insurance Promo Code">
            <button class="btn btn-success" type="submit">Apply Promo</button>
        </div>
    </form>

    <!-- Final Bill Table -->
    <table class="table table-bordered">
        <tr><th>Passenger</th><td><?= htmlspecialchars($bill['user_name']) ?></td></tr>
        <tr><th>Flight</th><td><?= $bill['startingAirport'] ?> → <?= $bill['destinationAirport'] ?> (<?= $bill['airline'] ?>)</td></tr>
        <tr><th>Seats Booked</th><td><?= $bill['seatsBooked'] ?></td></tr>
        <tr><th>Flight Arrival</th><td><?= $bill['arrivalTime'] ?></td></tr>
        <tr><th>Flight Fare</th><td>$<?= number_format($bill['flight_total'], 2) ?></td></tr>
        <tr><th>Cab Fare</th><td>$<?= number_format($bill['cab_total'], 2) ?></td></tr>
        <tr><th>Hotel</th><td>$<?= number_format($bill['hotel_total'], 2) ?></td></tr>
        <tr><th>Food</th><td>$<?= number_format($bill['food_total'], 2) ?></td></tr>
        <tr><th>Insurance</th><td>$<?= number_format($bill['insurance_total'], 2) ?></td></tr>
        <tr class="table-success"><th>Grand Total</th><td><strong>$<?= number_format($bill['grand_total'], 2) ?></strong></td></tr>
    </table>

    <!-- Pay Now Button -->
    <form method="POST" action="payment_gateway.php">
        <input type="hidden" name="bill_id" value="<?= $bill['id'] ?>">
        <input type="hidden" name="amount" value="<?= $bill['grand_total'] ?>">
        <button type="submit" class="btn btn-primary btn-lg">💳 Pay Now</button>
    </form>
</body>
</html>
