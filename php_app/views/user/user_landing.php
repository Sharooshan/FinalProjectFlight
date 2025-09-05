<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "finalproject";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$allFlights = [];
$result = $conn->query("SELECT * FROM flightsfuture");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $allFlights[] = $row;
    }
}

// Helper: convert ISO duration (PT3H2M) → minutes
function parseDuration($iso)
{
    preg_match('/PT(\d+H)?(\d+M)?/', $iso, $m);
    $hours = isset($m[1]) ? (int) rtrim($m[1], 'H') : 0;
    $mins = isset($m[2]) ? (int) rtrim($m[2], 'M') : 0;
    return $hours * 60 + $mins;
}

// Helper: format ISO duration to "Xh Ym"
function formatDuration($iso)
{
    preg_match('/PT(\d+H)?(\d+M)?/', $iso, $m);
    $hours = isset($m[1]) ? (int) rtrim($m[1], 'H') : 0;
    $mins = isset($m[2]) ? (int) rtrim($m[2], 'M') : 0;
    return ($hours ? $hours . 'h ' : '') . ($mins ? $mins . 'm' : '');
}

$predictionResult = null;
$recommendations = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $origin = $_POST['origin'];
    $destination = $_POST['destination'];
    $flightDate = $_POST['flightDate'];
    $travelClass = $_POST['travelClass'];
    $stops = (int)$_POST['stops'];
    $isRefundable = isset($_POST['isRefundable']) ? 1 : 0;
    $distance = $_POST['distance'] ?? 500;

    $elapsedDays = max(0, round((strtotime($flightDate) - time()) / (60 * 60 * 24)));

    // Find flight duration if exists
    $durationMinutes = 0;
    foreach ($allFlights as $f) {
        if (
            strtoupper($f['startingAirport']) === strtoupper($origin) &&
            strtoupper($f['destinationAirport']) === strtoupper($destination) &&
            $f['flightDate'] === $flightDate
        ) {
            $durationMinutes = parseDuration($f['travelDuration']);
            break;
        }
    }

    // Prepare payload for ML
    $payload = [
        "elapsedDays" => $elapsedDays,
        "isBasicEconomy" => $travelClass == 'Economy' ? 1 : 0,
        "isRefundable" => $isRefundable,
        "isNonStop" => $stops == 0 ? 1 : 0,
        "travelDurationMinutes" => $durationMinutes,
        "totalTravelDistance" => $distance,
        "baseFare" => 0,
        "seatsRemaining" => 10,
        "segmentsDistance_total" => $distance,
        "segmentsDepartureTimeEpochSeconds_total" => 0,
        "segmentsArrivalTimeEpochSeconds_total" => 0,
        "numSegments" => $stops + 1
    ];

    // Call Flask API
    $ch = curl_init("http://127.0.0.1:5000/predict");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response !== false) {
        $resp = json_decode($response, true);
        $predictedFare = $resp['predicted_fare'] ?? null;
        if ($predictedFare !== null) {
            if ($predictedFare < 100) $status = "Low";
            elseif ($predictedFare > 500) $status = "High";
            else $status = "OK";
            $predictionResult = [
                'origin' => $origin,
                'destination' => $destination,
                'flightDate' => $flightDate,
                'predictedFare' => $predictedFare,
                'status' => $status
            ];

            // Build recommendations
            $routeFlights = array_filter($allFlights, fn($f) => strtoupper($f['startingAirport']) === strtoupper($origin) && strtoupper($f['destinationAirport']) === strtoupper($destination));

            if (!empty($routeFlights)) {
                // Cheapest
                $cheapest = null;
                $minFare = PHP_INT_MAX;
                foreach ($routeFlights as $f) {
                    if ($f['totalFare'] < $minFare) {
                        $cheapest = $f;
                        $minFare = $f['totalFare'];
                    }
                }

                // Nearest
                $nearest = null;
                $minDiff = PHP_INT_MAX;
                foreach ($routeFlights as $f) {
                    $diff = abs($f['totalFare'] - $predictedFare);
                    if ($diff < $minDiff) {
                        $nearest = $f;
                        $minDiff = $diff;
                    }
                }

                // Recommended = shortest duration with seats
                $recommended = null;
                $minDuration = PHP_INT_MAX;
                foreach ($routeFlights as $f) {
                    $dur = parseDuration($f['travelDuration']);
                    if ($f['seatsRemaining'] > 0 && $dur < $minDuration) {
                        $recommended = $f;
                        $minDuration = $dur;
                    }
                }

                $recommendations = [
                    'recommended' => $recommended,
                    'nearest' => $nearest,
                    'cheapest' => $cheapest
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>IntelliFlight - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f4f8;
            font-family: 'Segoe UI', sans-serif;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #00c6ff, #0072ff);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }

        .hero h3 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .hero p {
            font-size: 1rem;
        }

        /* Search Form Card */
        .search-card {
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .search-card .form-select,
        .search-card .form-control {
            border-radius: 8px;
        }

        .search-card button {
            background: #0072ff;
            border: none;
        }

        .search-card button:hover {
            background: #0056b3;
        }

        /* Prediction Card */
        .prediction-card {
            background: #fff;
            border-left: 5px solid #0072ff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 8px;
            color: white;
        }

        .status-Low {
            background-color: #28a745;
        }

        /* green */
        .status-OK {
            background-color: #ffc107;
        }

        /* yellow/orange */
        .status-High {
            background-color: #dc3545;
        }

        /* red */


        /* Flight Cards */
        .flight-card {
            background: linear-gradient(135deg, #e0f7fa, #ffffff);
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            margin-bottom: 15px;
        }

        .flight-card:hover {
            transform: translateY(-5px);
        }

        .flight-card h6 {
            font-weight: 600;
        }

        .flight-card .btn-success {
            background: #0072ff;
            border: none;
        }

        .flight-card .btn-success:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>

        <div class="container-fluid p-4" style="flex:1;">
            <!-- Hero Section -->
            <div class="hero">
                <h3>Flight Prediction & Booking</h3>
                <p>Find the best fares and book your flights instantly ✈️</p>
            </div>

            <!-- Search Form -->
            <div class="search-card">
                <form method="POST" action="">
                    <div class="row g-2">
                        <div class="col-md-2">
                            <select class="form-select" name="origin" required>
                                <option value="" disabled selected>Select Origin</option>
                                <option value="ATL">Atlanta (ATL)</option>
                                <option value="BOS">Boston (BOS)</option>
                                <option value="LHR">London Heathrow (LHR)</option>
                                <option value="CMB">Colombo (CMB)</option>
                                <option value="DXB">Dubai (DXB)</option>
                                <option value="JFK">New York JFK (JFK)</option>
                                <option value="NRT">Tokyo Narita (NRT)</option>
                                <option value="SIN">Singapore (SIN)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="destination" required>
                                <option value="" disabled selected>Select Destination</option>
                                <option value="ATL">Atlanta (ATL)</option>
                                <option value="BOS">Boston (BOS)</option>
                                <option value="LHR">London Heathrow (LHR)</option>
                                <option value="CMB">Colombo (CMB)</option>
                                <option value="DXB">Dubai (DXB)</option>
                                <option value="JFK">New York JFK (JFK)</option>
                                <option value="NRT">Tokyo Narita (NRT)</option>
                                <option value="SIN">Singapore (SIN)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="flightDate" required>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="travelClass">
                                <option value="Economy" selected>Economy</option>
                                <option value="Business">Business</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <input type="number" class="form-control" name="stops" placeholder="Stops" min="0" required>
                        </div>
                        <div class="col-md-1 d-flex align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="isRefundable" id="isRefundable">
                                <label class="form-check-label" for="isRefundable">Refundable</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2">Predict Fare</button>
                </form>
            </div>

            <!-- Prediction Result -->
            <?php if ($predictionResult): ?>
                <div class="prediction-card">
                    <h5>Prediction Result</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th>Origin</th>
                            <th>Destination</th>
                            <th>Flight Date</th>
                            <th>Predicted Fare</th>
                            <th>Status</th>
                        </tr>
                        <tr>
                            <td><?= htmlspecialchars($predictionResult['origin']) ?></td>
                            <td><?= htmlspecialchars($predictionResult['destination']) ?></td>
                            <td><?= htmlspecialchars($predictionResult['flightDate']) ?></td>
                            <td>$<?= number_format($predictionResult['predictedFare'], 2) ?></td>
                            <td><span class="status status-<?= $predictionResult['status'] ?>"><?= $predictionResult['status'] ?></span></td>
                        </tr>
                    </table>

                    <!-- Recommended Flights -->
                    <?php if (!empty($recommendations)): ?>
                        <div class="row mt-3">
                            <?php foreach (['recommended', 'nearest', 'cheapest'] as $type):
                                $flight = $recommendations[$type] ?? null;
                                if (!$flight) continue;
                            ?>
                                <div class="col-md-4">
                                    <div class="flight-card">
                                        <h6>
                                            <?= $type === 'recommended' ? '⭐ Recommended Flight' : ($type === 'nearest' ? '🎯 Nearest Price' : '💰 Cheapest Flight') ?>
                                        </h6>
                                        <p>
                                            Airline: <?= htmlspecialchars($flight['segmentsAirlineName'] ?? 'Unknown Airline') ?><br>
                                            From: <?= htmlspecialchars($flight['startingAirport']) ?> → <?= htmlspecialchars($flight['destinationAirport']) ?><br>
                                            Date: <?= htmlspecialchars($flight['flightDate']) ?><br>
                                            Departure: <?= htmlspecialchars($flight['segmentsDepartureTimeRaw'] ?? 'N/A') ?><br>
                                            Arrival: <?= htmlspecialchars($flight['segmentsArrivalTimeRaw'] ?? 'N/A') ?><br>
                                            Duration: <?= formatDuration($flight['travelDuration'] ?? 'PT0H0M') ?><br>
                                            Fare: $<?= number_format($flight['totalFare'] ?? 0, 2) ?><br>
                                            Seats Remaining: <?= htmlspecialchars($flight['seatsRemaining'] ?? 0) ?>
                                        </p>

                                        <form method="POST" action="book_flight.php">
                                            <?php foreach ($flight as $key => $val): ?>
                                                <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($val) ?>">
                                            <?php endforeach; ?>
                                            <button type="submit" class="btn btn-success">Book Now</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>

</html>