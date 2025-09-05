<?php
session_start();
$pdo = include '../../config/db.php';

// Check login
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Please login to book a cab.");
}

// Fetch latest flight booking
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$user_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$booking) {
    die("No booking found. Please book a flight first.");
}

// Default pickup & drop locations
$pickupLocation = "Car Park Area - Look for INTELLIFLIGHT board";
$dropLocation   = $booking['destinationAirport'];
$pickupDate     = date("Y-m-d", strtotime($booking['arrivalTime']));
$pickupTime     = date("H:i", strtotime($booking['arrivalTime']));

// Fetch available cars (seats >= booked seats)
$stmtCars = $pdo->prepare("SELECT * FROM cars WHERE seats >= ?");
$stmtCars->execute([$booking['seatsBooked']]);
$cars = $stmtCars->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dressCode = $_POST['dressCode'] ?? '';
    $carId     = $_POST['car_id'];

    // Fetch car details
    $stmtCar = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
    $stmtCar->execute([$carId]);
    $car = $stmtCar->fetch(PDO::FETCH_ASSOC);
    if (!$car) die("Invalid car selection.");

    // Calculate cab fare (example fixed distance)
    $distance = 20; // km
    $cabFare  = $distance * $car['price_per_km'];

    // Save cab booking
    $stmtSave = $pdo->prepare("INSERT INTO cab_bookings 
        (user_id, booking_id, pickupLocation, dropLocation, pickupDate, pickupTime, dressCode, car_id, estimatedDistance, cabFare) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtSave->execute([$user_id, $booking['id'], $pickupLocation, $dropLocation, $pickupDate, $pickupTime, $dressCode, $carId, $distance, $cabFare]);

    // Update final bill
    $stmtBillCheck = $pdo->prepare("SELECT * FROM final_bills WHERE user_id = ? AND booking_id = ?");
    $stmtBillCheck->execute([$user_id, $booking['id']]);
    $bill = $stmtBillCheck->fetch(PDO::FETCH_ASSOC);

    $flightTotal = $booking['finalTotal'];
    $hotelTotal  = $bill['hotel_total'] ?? 0;
    $cabTotal    = $bill['cab_total'] ?? 0;
    $foodTotal   = $bill['food_total'] ?? 0;

    $cabTotal += $cabFare;
    $grandTotal = $flightTotal + $hotelTotal + $cabTotal + $foodTotal;

    if ($bill) {
        $stmtUpdate = $pdo->prepare("UPDATE final_bills SET cab_total = ?, grand_total = ? WHERE id = ?");
        $stmtUpdate->execute([$cabTotal, $grandTotal, $bill['id']]);
    } else {
        $stmtInsert = $pdo->prepare("INSERT INTO final_bills 
            (user_id, booking_id, flight_total, hotel_total, cab_total, food_total, grand_total) 
            VALUES (?, ?, ?, 0, ?, 0, ?)");
        $stmtInsert->execute([$user_id, $booking['id'], $flightTotal, $cabFare, $grandTotal]);
    }

    echo "<div class='alert alert-success mt-3'>
            ✅ Cab booked successfully to $dropLocation! <br>
            💰 Cab Fare: $$cabFare <br>
            🧾 Flight + Hotel + Cab + Food Total: $$grandTotal
          </div>";

    header("Refresh: 2; url=finalBill.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cab Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .booking-form {
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .booking-form:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="container-fluid p-4" style="flex:1;">
        <h2 class="mb-4">Book Your Cab</h2>

        <div class="booking-form">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Pickup Location</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($pickupLocation) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Drop Location</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($dropLocation) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pickup Date</label>
                    <input type="date" class="form-control" value="<?= htmlspecialchars($pickupDate) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pickup Time</label>
                    <input type="time" class="form-control" value="<?= htmlspecialchars($pickupTime) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Dress Code (optional)</label>
                    <input type="text" name="dressCode" class="form-control" placeholder="Example: Red Shirt">
                </div>

                <div class="mb-3">
                    <label class="form-label">Select Car</label>
                    <select name="car_id" class="form-control" required>
                        <?php foreach ($cars as $car): ?>
                            <option value="<?= $car['id'] ?>">
                                <?= htmlspecialchars($car['car_name']) ?>
                                (<?= $car['seats'] ?> seats)
                                - $<?= number_format($car['price_per_km'], 2) ?>/km
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100">Book Cab</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
