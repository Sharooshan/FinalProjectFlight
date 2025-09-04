<?php
session_start();
$pdo = include '../../config/db.php';

// Check login
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) die("Please log in first.");

// Get booking ID from URL or latest flight
$booking_id = $_GET['booking_id'] ?? null;
if (!$booking_id) {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$booking) die("No booking found. Please book a flight first.");
    $booking_id = $booking['id'];
} else {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$booking) die("Booking not found.");
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
$destinationCityName = $airportToCity[$booking['destinationAirport']] ?? $booking['destinationAirport'];
$guests = $booking['seatsBooked'];

// Fetch hotels, foods, and cars
$stmtHotels = $pdo->prepare("SELECT * FROM hotels WHERE city = ?");
$stmtHotels->execute([$destinationCityName]);
$hotels = $stmtHotels->fetchAll(PDO::FETCH_ASSOC);

$currentTime = date("H:i:s");
$stmtFoods = $pdo->prepare("SELECT * FROM foods WHERE available_from <= ? AND available_to >= ?");
$stmtFoods->execute([$currentTime, $currentTime]);
$foods = $stmtFoods->fetchAll(PDO::FETCH_ASSOC);

$stmtCars = $pdo->prepare("SELECT * FROM cars WHERE seats >= ?");
$stmtCars->execute([$guests]);
$cars = $stmtCars->fetchAll(PDO::FETCH_ASSOC);

// Define packages
$packages = [
    'Average' => ['hotel' => 1, 'food' => 2, 'cab' => 1],
    'Best' => ['hotel' => 2, 'food' => 3, 'cab' => 1],
    'Premium' => ['hotel' => 3, 'food' => 5, 'cab' => 2]
];

// Handle package booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pkg = $_POST['package'];
    if (!isset($packages[$pkg])) die("Invalid package selection.");

    $hotelCount = min($packages[$pkg]['hotel'], count($hotels));
    $selectedHotels = array_slice($hotels, 0, $hotelCount);
    $hotelFare = array_sum(array_column($selectedHotels, 'price_per_night'));

    $foodCount = min($packages[$pkg]['food'], count($foods));
    $selectedFoods = array_slice($foods, 0, $foodCount);
    $foodFare = array_sum(array_column($selectedFoods, 'price'));

    $carCount = min($packages[$pkg]['cab'], count($cars));
    $selectedCars = array_slice($cars, 0, $carCount);
    $distance = 20; 
    $cabFare = 0;
    foreach ($selectedCars as $car) {
        $cabFare += $car['price_per_km'] * $distance;
    }

    // Save hotel bookings
    foreach ($selectedHotels as $hotel) {
        $stmtSaveHotel = $pdo->prepare("INSERT INTO hotel_bookings
            (user_id, booking_id, hotel_id, checkin, checkout, rooms, guests, total_price)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtSaveHotel->execute([$user_id, $booking_id, $hotel['id'], date('Y-m-d'), date('Y-m-d', strtotime('+1 day')), 1, $guests, $hotel['price_per_night']]);
    }

    // Save food bookings
    foreach ($selectedFoods as $food) {
        $stmtSaveFood = $pdo->prepare("INSERT INTO food_bookings
            (user_id, booking_id, food_id, quantity, total_price)
            VALUES (?, ?, ?, ?, ?)");
        $stmtSaveFood->execute([$user_id, $booking_id, $food['id'], 1, $food['price']]);
    }

    // Save cab bookings
    foreach ($selectedCars as $car) {
        $stmtSaveCab = $pdo->prepare("INSERT INTO cab_bookings
            (user_id, booking_id, pickupLocation, dropLocation, pickupDate, pickupTime, car_id, estimatedDistance, cabFare)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtSaveCab->execute([$user_id, $booking_id, "Airport Pickup", $booking['destinationAirport'], date('Y-m-d'), date('H:i'), $car['id'], $distance, $car['price_per_km'] * $distance]);
    }

    $flightTotal = $booking['finalTotal'];
    $grandTotal = $flightTotal + $hotelFare + $foodFare + $cabFare;

    $stmtBillCheck = $pdo->prepare("SELECT * FROM final_bills WHERE user_id = ? AND booking_id = ?");
    $stmtBillCheck->execute([$user_id, $booking_id]);
    $bill = $stmtBillCheck->fetch(PDO::FETCH_ASSOC);

    if ($bill) {
        $stmtUpdate = $pdo->prepare("UPDATE final_bills SET hotel_total=?, food_total=?, cab_total=?, grand_total=? WHERE id=?");
        $stmtUpdate->execute([$hotelFare, $foodFare, $cabFare, $grandTotal, $bill['id']]);
    } else {
        $stmtInsert = $pdo->prepare("INSERT INTO final_bills
            (user_id, booking_id, flight_total, hotel_total, food_total, cab_total, grand_total)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtInsert->execute([$user_id, $booking_id, $flightTotal, $hotelFare, $foodFare, $cabFare, $grandTotal]);
    }

    echo "<div class='alert alert-success mt-3'>
            ✅ Package '$pkg' booked successfully! <br>
            💰 Hotel: $$hotelFare, Food: $$foodFare, Cab: $$cabFare <br>
            🧾 Total: $$grandTotal
          </div>";
    header("Refresh: 2; url=finalBill.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Combo Booking Packages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="container-fluid p-4" style="flex:1;">
        <h2 class="mb-4">Combo Booking Packages for <?= htmlspecialchars($destinationCityName) ?></h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Select Package</label>
                <select name="package" class="form-control" required>
                    <?php foreach ($packages as $pkgName => $vals): ?>
                        <option value="<?= $pkgName ?>"><?= $pkgName ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Book Package</button>
        </form>
    </div>
</div>
</body>
</html>
