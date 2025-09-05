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

// Get booking ID from redirect
$bookingId = $_GET['id'] ?? 0;

if ($bookingId == 0) {
    echo "<p style='color:red'>No booking selected.</p>";
    exit();
}

// Fetch booking details
$sql = "SELECT * FROM bookings WHERE id = :bookingId AND user_id = :userId";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':bookingId' => $bookingId,
    ':userId' => $userId
]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo "<p style='color:red'>Booking not found or you don't have permission to view it.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Options</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Booking option card styling */
        .option-card {
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            background: #ffffff;
        }

        .option-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }

        .option-card .card-header {
            font-weight: bold;
            font-size: 1.1rem;
        }

        .btn-option {
            width: 100%;
            font-weight: bold;
            transition: background 0.2s;
            margin-top: 10px;
            color: #fff;
            border: none;
        }

        .btn-success.btn-option { background: #28a745; }
        .btn-success.btn-option:hover { background: #218838; }

        .btn-primary.btn-option { background: #007bff; }
        .btn-primary.btn-option:hover { background: #0069d9; }

        .btn-warning.btn-option { background: #ffc107; color: #212529; }
        .btn-warning.btn-option:hover { background: #e0a800; }

        .btn-danger.btn-option { background: #dc3545; }
        .btn-danger.btn-option:hover { background: #c82333; }

        .btn-dark.btn-option { background: #343a40; }
        .btn-dark.btn-option:hover { background: #23272b; }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="container-fluid p-4" style="flex:1;">
        <h3 class="mb-4">Your Booking Details</h3>

        <div class="card mb-4">
            <div class="card-header">Flight Booking</div>
            <div class="card-body">
                <p><strong>Airline:</strong> <?= htmlspecialchars($booking['airline']) ?></p>
                <p><strong>Flight:</strong> <?= htmlspecialchars($booking['startingAirport']) ?> → <?= htmlspecialchars($booking['destinationAirport']) ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($booking['flightDate']) ?></p>
                <p><strong>Departure:</strong> <?= htmlspecialchars($booking['departureTime']) ?> | <strong>Arrival:</strong> <?= htmlspecialchars($booking['arrivalTime']) ?></p>
                <p><strong>Duration:</strong> <?= htmlspecialchars($booking['duration']) ?> | <strong>Non-Stop:</strong> <?= $booking['isNonStop'] ? 'Yes' : 'No' ?></p>
                <p><strong>Seats Booked:</strong> <?= htmlspecialchars($booking['seatsBooked']) ?></p>
                <p><strong>Total Fare:</strong> $<?= htmlspecialchars($booking['finalTotal']) ?></p>
            </div>
        </div>

        <h4 class="mb-4">Select Additional Options</h4>
        <div class="row">

            <!-- Flight Only -->
            <div class="col-md-4">
                <div class="option-card mb-3 border-success">
                    <div class="card-header bg-success text-white">Flight Only</div>
                    <div class="card-body">
                        <p>Proceed with flight booking only.</p>
                        <a href="finalBill.php?booking_id=<?= $bookingId ?>&option=flight" class="btn btn-success btn-option">Choose Flight Only</a>
                    </div>
                </div>
            </div>

            <!-- Cab Only -->
            <div class="col-md-4">
                <div class="option-card mb-3 border-primary">
                    <div class="card-header bg-primary text-white">Cab Service</div>
                    <div class="card-body">
                        <p>Pick-up & drop service from airport.</p>
                        <a href="cabBooking.php?booking_id=<?= $bookingId ?>" class="btn btn-primary btn-option">Add Cab</a>
                    </div>
                </div>
            </div>

            <!-- Hotel Only -->
            <div class="col-md-4">
                <div class="option-card mb-3 border-warning">
                    <div class="card-header bg-warning text-dark">Hotel</div>
                    <div class="card-body">
                        <p>Reserve a hotel at your destination.</p>
                        <a href="hotelBooking.php?booking_id=<?= $bookingId ?>" class="btn btn-warning btn-option">Add Hotel</a>
                    </div>
                </div>
            </div>

            <!-- Food Only -->
            <div class="col-md-4">
                <div class="option-card mb-3 border-danger">
                    <div class="card-header bg-danger text-white">Food</div>
                    <div class="card-body">
                        <p>Pre-book meals during your trip.</p>
                        <a href="foodBooking.php?booking_id=<?= $bookingId ?>" class="btn btn-danger btn-option">Add Food</a>
                    </div>
                </div>
            </div>

            <!-- Combo Pack -->
            <div class="col-md-4">
                <div class="option-card mb-3 border-dark">
                    <div class="card-header bg-dark text-white">Combo Pack</div>
                    <div class="card-body">
                        <p>Get Cab + Hotel + Food as one package.</p>
                        <a href="comboBooking.php?booking_id=<?= $bookingId ?>" class="btn btn-dark btn-option">Choose Combo</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>
