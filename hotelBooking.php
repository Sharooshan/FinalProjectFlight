<?php
session_start();
$pdo = include '../../config/db.php';

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Please log in first.");
}

// Fetch latest flight booking for the user
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$user_id]);
$flight = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$flight) {
    die("No flight booking found. Please book a flight first.");
}

// Map airport codes to city names
$airportToCity = [
    'BOS' => 'Boston',
    'ATL' => 'Atlanta',
    'CMB' => 'Colombo',
    'DXB' => 'Dubai',
    'JFK' => 'New York',
    'NRT' => 'Tokyo',
    'SIN' => 'Singapore',
    'LHR' => 'London'
];
$destinationCityName = $airportToCity[$flight['destinationAirport']] ?? $flight['destinationAirport'];
$guests = $flight['seatsBooked'];

// Fetch hotels in that city
$stmt = $pdo->prepare("SELECT * FROM hotels WHERE city = ?");
$stmt->execute([$destinationCityName]);
$hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle hotel booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hotelId = $_POST['hotel_id'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $rooms = max(1, (int)$_POST['rooms']);

    // Calculate nights
    $nights = (strtotime($checkout) - strtotime($checkin)) / (60 * 60 * 24);
    if ($nights < 1) $nights = 1;

    // Fetch selected hotel details
    $stmtHotel = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmtHotel->execute([$hotelId]);
    $hotel = $stmtHotel->fetch(PDO::FETCH_ASSOC);
    if (!$hotel) die("Invalid hotel selection.");

    // Calculate hotel fare
    $hotelFare = $hotel['price_per_night'] * $nights * $rooms;

    // Save hotel booking
    $stmtSave = $pdo->prepare("INSERT INTO hotel_bookings
        (user_id, booking_id, hotel_id, checkin, checkout, rooms, guests, total_price)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtSave->execute([$user_id, $flight['id'], $hotelId, $checkin, $checkout, $rooms, $guests, $hotelFare]);

    // Update final bill
    $stmtBillCheck = $pdo->prepare("SELECT * FROM final_bills WHERE user_id = ? AND booking_id = ?");
    $stmtBillCheck->execute([$user_id, $flight['id']]);
    $bill = $stmtBillCheck->fetch(PDO::FETCH_ASSOC);

    $flightTotal = $flight['finalTotal'];
    $cabTotal = $bill['cab_total'] ?? 0;
    $hotelTotal = $bill['hotel_total'] ?? 0;
    $foodTotal = $bill['food_total'] ?? 0;

    $hotelTotal += $hotelFare;
    $grandTotal = $flightTotal + $cabTotal + $hotelTotal + $foodTotal;

    if ($bill) {
        $stmtBillUpdate = $pdo->prepare("UPDATE final_bills SET hotel_total = ?, grand_total = ? WHERE id = ?");
        $stmtBillUpdate->execute([$hotelTotal, $grandTotal, $bill['id']]);
    } else {
        $stmtBillInsert = $pdo->prepare("INSERT INTO final_bills
            (user_id, booking_id, flight_total, cab_total, hotel_total, food_total, grand_total)
            VALUES (?, ?, ?, 0, ?, 0, ?)");
        $stmtBillInsert->execute([$user_id, $flight['id'], $flightTotal, $hotelFare, $grandTotal]);
    }

    echo "<div class='alert alert-success mt-3'>
            ✅ Hotel booked successfully! <br>
            💰 Hotel Fare: $$hotelFare <br>
            🧾 Flight + Cab + Hotel + Food Total: $$grandTotal
          </div>";
    header("Refresh: 2; url=finalBill.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hotel Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hotel-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 12px;
        }
        .hotel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="container-fluid p-4" style="flex:1;">
        <h2 class="mb-4">Available Hotels in <?= htmlspecialchars($destinationCityName) ?></h2>

        <?php if ($hotels): ?>
            <div class="row">
                <?php foreach ($hotels as $hotel): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card hotel-card">
                            <img src="<?= htmlspecialchars($hotel['photo_url'] ?? 'default-hotel.jpg') ?>" class="card-img-top" alt="Hotel Photo">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($hotel['name']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($hotel['amenities']) ?></p>
                                <p><strong>Price:</strong> $<?= $hotel['price_per_night'] ?>/night</p>
                                <form method="post">
                                    <input type="hidden" name="hotel_id" value="<?= $hotel['id'] ?>">
                                    <div class="mb-2">
                                        <label>Check-in:</label>
                                        <input type="date" name="checkin" class="form-control" value="<?= date("Y-m-d", strtotime($flight['arrivalTime'])) ?>" required>
                                    </div>
                                    <div class="mb-2">
                                        <label>Check-out:</label>
                                        <input type="date" name="checkout" class="form-control" value="<?= date("Y-m-d", strtotime($flight['arrivalTime'] . ' +1 day')) ?>" required>
                                    </div>
                                    <div class="mb-2">
                                        <label>Rooms:</label>
                                        <input type="number" name="rooms" value="1" min="1" class="form-control">
                                    </div>
                                    <div class="mb-2">
                                        <label>Guests:</label>
                                        <input type="number" name="guests" value="<?= $guests ?>" min="1" max="<?= $guests ?>" class="form-control" readonly>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Book Hotel</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No hotels available in this city.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>