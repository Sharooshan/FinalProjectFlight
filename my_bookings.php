<?php
session_start();
$pdo = include '../../config/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch user info
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Fetch all bookings for this user (latest first)
$stmtBookings = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC");
$stmtBookings->execute([$userId]);
$bookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

// Fetch latest final bill for this user (last booking)
$stmtBill = $pdo->prepare("
    SELECT fb.*, b.startingAirport, b.destinationAirport, b.airline, b.seatsBooked, b.arrivalTime
    FROM final_bills fb
    JOIN bookings b ON fb.booking_id = b.id
    WHERE fb.user_id = ?
    ORDER BY fb.created_at DESC
    LIMIT 1
");
$stmtBill->execute([$userId]);
$latestBill = $stmtBill->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid mt-4">

    <div class="row">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col-md-8 p-4">
            <h3>My Bookings</h3>

            <?php if ($latestBill): ?>
                <h5>Latest Booking (Most Recent)</h5>
                <table class="table table-bordered mb-4">
                    <tr><th>Passenger</th><td><?= htmlspecialchars($user['fullName']) ?></td></tr>
                    <tr><th>Flight</th><td><?= $latestBill['startingAirport'] ?> → <?= $latestBill['destinationAirport'] ?> (<?= $latestBill['airline'] ?>)</td></tr>
                    <tr><th>Seats Booked</th><td><?= $latestBill['seatsBooked'] ?></td></tr>
                    <tr><th>Flight Arrival</th><td><?= $latestBill['arrivalTime'] ?></td></tr>
                    <tr><th>Flight Fare</th><td>$<?= number_format($latestBill['flight_total'],2) ?></td></tr>
                    <tr><th>Cab Fare</th><td>$<?= number_format($latestBill['cab_total'],2) ?></td></tr>
                    <tr><th>Hotel</th><td>$<?= number_format($latestBill['hotel_total'],2) ?></td></tr>
                    <tr><th>Food</th><td>$<?= number_format($latestBill['food_total'],2) ?></td></tr>
                    <tr><th>Insurance</th><td>$<?= number_format($latestBill['insurance_total'],2) ?></td></tr>
                    <tr class="table-success"><th>Grand Total</th><td><strong>$<?= number_format($latestBill['grand_total'],2) ?></strong></td></tr>
                </table>
                <p class="text-muted">Payment for the latest booking is **not allowed here**.</p>
            <?php endif; ?>

            <hr>
            <h5>All Bookings</h5>
            <?php if (count($bookings) > 0): ?>
                <table class="table table-striped mt-3">
                    <thead>
                        <tr>
                            <th>Flight</th>
                            <th>Date</th>
                            <th>From → To</th>
                            <th>Seats</th>
                            <th>Fare per Seat</th>
                            <th>Total</th>
                            <th>Booked At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= htmlspecialchars($booking['airline'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($booking['flightDate'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($booking['startingAirport']) ?> → <?= htmlspecialchars($booking['destinationAirport']) ?></td>
                                <td><?= htmlspecialchars($booking['seatsBooked']) ?></td>
                                <td>$<?= htmlspecialchars($booking['totalFare']) ?></td>
                                <td>$<?= htmlspecialchars($booking['finalTotal'] ?? ($booking['totalFare'] * $booking['seatsBooked'])) ?></td>
                                <td><?= htmlspecialchars($booking['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">You have no bookings yet.</p>
            <?php endif; ?>
        </div>

        <!-- Right Sidebar - Top Destinations -->
        <div class="col-md-2 bg-light p-3" style="border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); height: 800px; overflow-y: auto;">
            <h5 class="mb-3" style="color: #0b5fff; font-weight: 600;">🌍 Top Destinations</h5>
            <div class="dest-card mb-3 p-2" style="background: #e0f2fe; border-radius: 8px;">🗼 Paris, France<br>Iconic Eiffel Tower & Cafés</div>
            <div class="dest-card mb-3 p-2" style="background: #fef3c7; border-radius: 8px;">🗽 New York, USA<br>Statue of Liberty & Broadway</div>
            <div class="dest-card mb-3 p-2" style="background: #d1fae5; border-radius: 8px;">🕌 Dubai, UAE<br>Burj Khalifa & Desert Safari</div>
            <div class="dest-card mb-3 p-2" style="background: #fde68a; border-radius: 8px;">🗻 Tokyo, Japan<br>Mount Fuji & Cherry Blossoms</div>
            <div class="dest-card mb-3 p-2" style="background: #fcd5ce; border-radius: 8px;">🏰 London, UK<br>Buckingham Palace & Thames River</div>
            <div class="dest-card mb-3 p-2" style="background: #dbeafe; border-radius: 8px;">🕌 Istanbul, Turkey<br>Hagia Sophia & Bosphorus</div>
        </div>
    </div>
</div>
</body>
</html>
