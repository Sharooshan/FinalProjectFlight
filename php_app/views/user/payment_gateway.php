<?php
session_start();
$pdo = include '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Please login to proceed to payment.");
}

$user_id = $_SESSION['user_id'];
$bill_id  = $_POST['bill_id'] ?? null;

if (!$bill_id) {
    die("Bill ID missing. Please go back and try again.");
}

// Fetch user details
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    die("User not found.");
}

// Fetch final bill details
$stmtBill = $pdo->prepare("SELECT fb.*, b.startingAirport, b.destinationAirport, b.seatsBooked, b.airline, b.arrivalTime 
                           FROM final_bills fb 
                           JOIN bookings b ON fb.booking_id = b.id 
                           WHERE fb.id = ? AND fb.user_id = ?");
$stmtBill->execute([$bill_id, $user_id]);
$bill = $stmtBill->fetch(PDO::FETCH_ASSOC);
if (!$bill) {
    die("Final bill not found.");
}

// Handle payment submission
$paymentMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $paymentMethod = $_POST['payment_method'];
    $paidAmount = $bill['grand_total'];
    
    // Save payment in database
    $stmtPay = $pdo->prepare("INSERT INTO payments (user_id, bill_id, payment_method, amount, paid_at) 
                              VALUES (?, ?, ?, ?, NOW())");
    $stmtPay->execute([$user_id, $bill_id, $paymentMethod, $paidAmount]);
    
    $paymentMessage = "✅ Payment successful via $paymentMethod. Amount paid: $$paidAmount";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Gateway</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Payment Gateway</h2>

    <?php if ($paymentMessage): ?>
        <div class="alert alert-success"><?= $paymentMessage ?></div>
    <?php endif; ?>

    <!-- User Details -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">User Information</div>
        <div class="card-body">
            <p><strong>Full Name:</strong> <?= htmlspecialchars($user['fullName']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Mobile:</strong> <?= htmlspecialchars($user['mobile']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($user['address'] ?? '-') ?></p>
            <p><strong>Date of Birth:</strong> <?= htmlspecialchars($user['dob'] ?? '-') ?></p>
        </div>
    </div>

    <!-- Final Bill Summary -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">Final Bill Summary</div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr><th>Flight</th><td><?= $bill['startingAirport'] ?> → <?= $bill['destinationAirport'] ?> (<?= $bill['airline'] ?>)</td></tr>
                <tr><th>Seats Booked</th><td><?= $bill['seatsBooked'] ?></td></tr>
                <tr><th>Flight Fare</th><td>$<?= number_format($bill['flight_total'],2) ?></td></tr>
                <tr><th>Cab Fare</th><td>$<?= number_format($bill['cab_total'],2) ?></td></tr>
                <tr><th>Hotel</th><td>$<?= number_format($bill['hotel_total'],2) ?></td></tr>
                <tr><th>Food</th><td>$<?= number_format($bill['food_total'],2) ?></td></tr>
                <tr><th>Insurance</th><td>$<?= number_format($bill['insurance_total'],2) ?></td></tr>
                <tr class="table-primary"><th>Grand Total</th><td><strong>$<?= number_format($bill['grand_total'],2) ?></strong></td></tr>
            </table>
        </div>
    </div>

    <!-- Payment Options -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">Choose Payment Method</div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="bill_id" value="<?= $bill_id ?>">
                <div class="mb-3">
                    <select name="payment_method" class="form-control" required>
                        <option value="">-- Select Payment Method --</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Debit Card">Debit Card</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Paypal">Paypal</option>
                        <option value="Cash On Delivery">Cash On Delivery</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success btn-lg">💳 Pay Now $<?= number_format($bill['grand_total'],2) ?></button>
            </form>
        </div>
    </div>
</body>
</html>
