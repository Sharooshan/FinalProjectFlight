<?php
session_start();
$pdo = include '../../config/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user info
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Initialize flight variables from POST
$legId = $_POST['legId'] ?? '';
$flightDate = $_POST['flightDate'] ?? '';
$startingAirport = $_POST['startingAirport'] ?? '';
$destinationAirport = $_POST['destinationAirport'] ?? '';
$airline = $_POST['segmentsAirlineName'] ?? '';
$departureTime = $_POST['segmentsDepartureTimeRaw'] ?? '';
$arrivalTime = $_POST['segmentsArrivalTimeRaw'] ?? '';
$duration = $_POST['travelDuration'] ?? '';
$isNonStop = $_POST['isNonStop'] ?? '';
$totalFare = $_POST['totalFare'] ?? '';
$seatsRemaining = $_POST['seatsRemaining'] ?? 10;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seatsBooked'])) {
    $seatsBooked = intval($_POST['seatsBooked']);

    if ($seatsBooked > 0 && $seatsBooked <= $seatsRemaining) {
        $pdo->beginTransaction();
        try {
            $finalTotal = $totalFare * $seatsBooked;

            // Insert booking
            $sql = "INSERT INTO bookings 
                    (user_id, user_name, legId, flightDate, startingAirport, destinationAirport, airline, departureTime, arrivalTime, duration, isNonStop, totalFare, seatsBooked, finalTotal) 
                    VALUES 
                    (:user_id, :user_name, :legId, :flightDate, :startingAirport, :destinationAirport, :airline, :departureTime, :arrivalTime, :duration, :isNonStop, :totalFare, :seatsBooked, :finalTotal)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':user_name' => $userName,
                ':legId' => $legId,
                ':flightDate' => $flightDate,
                ':startingAirport' => $startingAirport,
                ':destinationAirport' => $destinationAirport,
                ':airline' => $airline,
                ':departureTime' => $departureTime,
                ':arrivalTime' => $arrivalTime,
                ':duration' => $duration,
                ':isNonStop' => $isNonStop,
                ':totalFare' => $totalFare,
                ':seatsBooked' => $seatsBooked,
                ':finalTotal' => $finalTotal
            ]);

            $lastBookingId = $pdo->lastInsertId();

            // Reduce seats
            $sql2 = "UPDATE flightsfuture 
                     SET seatsRemaining = seatsRemaining - :seatsBooked 
                     WHERE legId = :legId";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute([
                ':seatsBooked' => $seatsBooked,
                ':legId' => $legId
            ]);

            $pdo->commit();
            $seatsRemaining -= $seatsBooked;
            header("Location: bookingOptions.php?id=" . $lastBookingId);
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "<p style='color:red'>Error saving booking: " . $e->getMessage() . "</p>";
        }
    } else {
        $message = "<p style='color:red'>Invalid number of seats! Only $seatsRemaining left.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Flight</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f4f8;
            font-family: 'Segoe UI', sans-serif;
        }
        h3 {
            color: #0072ff;
        }
        .booking-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .flight-info {
            background: #e0f7fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        .flight-info p {
            margin: 3px 0;
        }
        .btn-primary {
            background: #0072ff;
            border: none;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        #totalFareDisplay {
            font-weight: bold;
            color: #0072ff;
        }
        .sidebar-col {
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .ad-img {
            border-radius: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <!-- Main Booking Area -->
        <div class="col-md-8 p-4">
            <h3 class="mb-4">Book a Flight</h3>
            <?= $message ?>

            <div class="booking-card">
                <form method="POST">
                    <input type="hidden" name="legId" value="<?= htmlspecialchars($legId) ?>">
                    <input type="hidden" name="flightDate" value="<?= htmlspecialchars($flightDate) ?>">
                    <input type="hidden" name="startingAirport" value="<?= htmlspecialchars($startingAirport) ?>">
                    <input type="hidden" name="destinationAirport" value="<?= htmlspecialchars($destinationAirport) ?>">
                    <input type="hidden" name="segmentsAirlineName" value="<?= htmlspecialchars($airline) ?>">
                    <input type="hidden" name="segmentsDepartureTimeRaw" value="<?= htmlspecialchars($departureTime) ?>">
                    <input type="hidden" name="segmentsArrivalTimeRaw" value="<?= htmlspecialchars($arrivalTime) ?>">
                    <input type="hidden" name="travelDuration" value="<?= htmlspecialchars($duration) ?>">
                    <input type="hidden" name="isNonStop" value="<?= htmlspecialchars($isNonStop) ?>">
                    <input type="hidden" name="totalFare" value="<?= htmlspecialchars($totalFare) ?>">
                    <input type="hidden" name="seatsRemaining" value="<?= htmlspecialchars($seatsRemaining) ?>">

                    <div class="flight-info">
                        <label>Flight Details</label>
                        <p><strong><?= htmlspecialchars($airline) ?></strong>: <?= htmlspecialchars($startingAirport) ?> → <?= htmlspecialchars($destinationAirport) ?> on <?= htmlspecialchars($flightDate) ?></p>
                        <p>Departure: <?= htmlspecialchars($departureTime) ?> | Arrival: <?= htmlspecialchars($arrivalTime) ?> | Duration: <?= htmlspecialchars($duration) ?> | Fare: $<?= htmlspecialchars($totalFare) ?></p>
                        <p>Seats Available: <?= htmlspecialchars($seatsRemaining) ?></p>
                    </div>

                    <div class="mb-3">
                        <label>Seats to Book</label>
                        <input type="number" id="seatsBooked" name="seatsBooked" class="form-control" min="1" max="<?= htmlspecialchars($seatsRemaining) ?>" required>
                        <p id="totalFareDisplay" class="mt-2">Total Fare: $<?= htmlspecialchars($totalFare) ?></p>
                    </div>

                    <button type="submit" class="btn btn-primary">Confirm Booking</button>
                </form>
            </div>
        </div>

        <!-- Ads / Sidebar Area -->
        <div class="col-md-2 p-3 sidebar-col">
            <h5>Sponsored</h5>
            <img src="https://via.placeholder.com/150" class="img-fluid ad-img" alt="Ad">
            <img src="https://via.placeholder.com/150" class="img-fluid ad-img" alt="Ad">
        </div>
    </div>
</div>

<script>
    const seatsInput = document.getElementById('seatsBooked');
    const totalDisplay = document.getElementById('totalFareDisplay');
    const pricePerSeat = <?= htmlspecialchars($totalFare) ?>;

    seatsInput.addEventListener('input', () => {
        let seats = parseInt(seatsInput.value) || 0;
        if (seats < 1) seats = 1;
        if (seats > <?= htmlspecialchars($seatsRemaining) ?>) seats = <?= htmlspecialchars($seatsRemaining) ?>;
        const total = (seats * pricePerSeat).toFixed(2);
        totalDisplay.textContent = 'Total Fare: $' + total;
    });
</script>
</body>
</html>
