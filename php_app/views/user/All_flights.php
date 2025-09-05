<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "finalproject";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch all flights
$allFlights = [];
$result = $conn->query("SELECT * FROM flightsfuture ORDER BY flightDate ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $allFlights[] = $row;
    }
}

// Build dynamic destination list
$destinations = [];
foreach ($allFlights as $f) {
    $destinations[$f['destinationAirport']] = $f['destinationAirport'];
}
asort($destinations); // Sort alphabetically

$routeFlights = $allFlights; // default: show all flights initially
$exactDateFlights = [];
$nearestFlights = [];
$dateMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $origin = $_POST['origin'] ?? '';  // <<< safe default
    $destination = $_POST['destination'] ?? null;
    $flightDate = $_POST['flightDate'] ?? null;
    $travelClass = $_POST['travelClass'] ?? null;
    $stops = isset($_POST['stops']) ? (int)$_POST['stops'] : null;
    $isRefundable = isset($_POST['isRefundable']) ? 1 : null;
    $sortBy = $_POST['sortBy'] ?? null;

    // Only filter if origin is set
    if ($origin !== '') {
        $filtered = array_filter($allFlights, function ($f) use ($origin, $destination, $stops, $isRefundable, $travelClass) {
            if (strtoupper($f['startingAirport']) !== strtoupper($origin)) return false;
            if ($destination && strtoupper($f['destinationAirport']) !== strtoupper($destination)) return false;

            if ($stops !== null) {
                $nonStop = (int)($f['isNonStop'] ?? 0);
                if ($stops === 0 && $nonStop === 0) return true;
                if ($stops === 1 && $nonStop === 1) return true;
                if (($stops === 0 && $nonStop === 1) || ($stops === 1 && $nonStop === 0)) return false;
            }

            if ($isRefundable !== null && (int)($f['isRefundable'] ?? 0) !== $isRefundable) return false;

            if ($travelClass) {
                $cabin = strtolower($f['segmentsCabinCode'] ?? '');
                if (strtolower($travelClass) !== $cabin) return false;
            }

            return true;
        });

        // Exact/nearest flights logic remains the same
        if ($flightDate) {
            $exactDateFlights = array_filter($filtered, fn($f) => $f['flightDate'] === $flightDate);

            if (!empty($exactDateFlights)) {
                $routeFlights = $exactDateFlights;
                $dateMessage = "Flights on your selected date ($flightDate):";
            } else {
                $start = strtotime($flightDate);
                $end = strtotime("+30 days", $start);
                $nearestFlights = array_filter($filtered, fn($f) => strtotime($f['flightDate']) >= $start && strtotime($f['flightDate']) <= $end);
                $routeFlights = $nearestFlights;
                $dateMessage = "No flights on selected date ($flightDate). Nearest available flights:";
            }
        } else {
            $routeFlights = $filtered;
        }

        // Sorting
        if ($sortBy) {
            if ($sortBy === 'price') {
                usort($routeFlights, fn($a, $b) => ($a['totalFare'] ?? 0) <=> ($b['totalFare'] ?? 0));
            } elseif ($sortBy === 'departure') {
                usort($routeFlights, fn($a, $b) => strtotime($a['segmentsDepartureTimeRaw'] ?? '') <=> strtotime($b['segmentsDepartureTimeRaw'] ?? ''));
            }
        }
    } else {
        // If origin not selected, show all flights (default)
        $routeFlights = $allFlights;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Flights Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Flight card styling */
        .flight-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .flight-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }

        .flight-card strong {
            color: #0072ff;
            font-size: 1.1rem;
        }

        .btn-book {
            width: 100%;
            font-weight: bold;
            transition: background 0.2s;
            background: #594fecff;
            color: #fff;
            border: none;
            margin-top: 10px;
        }

        .btn-book:hover {
            background: #4a3dcf;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <div class="container-fluid p-4" style="flex:1;">
            <h3>Available Flights</h3>

            <!-- Filter Form -->
            <form method="POST" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-2">
                        <select class="form-select" name="origin" required>
                            <option value="" disabled selected>Select Origin</option>
                            <?php
                            $origins = ['CMB' => 'Colombo', 'ATL' => 'Atlanta', 'BOS' => 'Boston', 'LHR' => 'London Heathrow', 'DXB' => 'Dubai', 'JFK' => 'New York JFK', 'NRT' => 'Tokyo Narita', 'SIN' => 'Singapore'];
                            foreach ($origins as $code => $name):
                            ?>
                                <option value="<?= $code ?>" <?= (isset($_POST['origin']) && $_POST['origin'] == $code) ? 'selected' : '' ?>><?= $name ?> (<?= $code ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select class="form-select" name="destination">
                            <option value="" selected>All Destinations</option>
                            <?php foreach ($destinations as $code => $name): ?>
                                <option value="<?= htmlspecialchars($code) ?>" <?= (isset($_POST['destination']) && $_POST['destination'] == $code) ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2"><input type="date" class="form-control" name="flightDate" value="<?= htmlspecialchars($_POST['flightDate'] ?? '') ?>"></div>

                    <div class="col-md-2">
                        <select class="form-select" name="travelClass">
                            <option value="">Any Class</option>
                            <option value="coach" <?= (isset($_POST['travelClass']) && $_POST['travelClass'] == 'coach') ? 'selected' : '' ?>>Economy</option>
                            <option value="business" <?= (isset($_POST['travelClass']) && $_POST['travelClass'] == 'business') ? 'selected' : '' ?>>Business</option>
                        </select>
                    </div>

                    <div class="col-md-1"><input type="number" class="form-control" name="stops" placeholder="Stops" value="<?= htmlspecialchars($_POST['stops'] ?? '') ?>"></div>

                    <div class="col-md-1 d-flex align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="isRefundable" id="isRefundable" <?= isset($_POST['isRefundable']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isRefundable">Refundable</label>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <select class="form-select" name="sortBy">
                            <option value="">Sort By</option>
                            <option value="price" <?= (isset($_POST['sortBy']) && $_POST['sortBy'] == 'price') ? 'selected' : '' ?>>Price (Low → High)</option>
                            <option value="departure" <?= (isset($_POST['sortBy']) && $_POST['sortBy'] == 'departure') ? 'selected' : '' ?>>Departure (Earliest)</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-2">Filter Flights</button>
            </form>

            <!-- Message about exact/nearest flights -->
            <?php if (!empty($_POST['flightDate'])): ?>
                <div class="alert alert-info"><?= $dateMessage ?></div>
            <?php endif; ?>

            <!-- Flights List -->
            <div class="row">
                <?php if (!empty($routeFlights)): ?>
                    <?php foreach ($routeFlights as $f): ?>
                        <div class="col-md-4 mb-3">
                            <div class="flight-card">
                                <strong><?= htmlspecialchars($f['segmentsAirlineName'] ?? 'Unknown Airline') ?></strong><br>
                                From: <?= htmlspecialchars($f['startingAirport']) ?> → <?= htmlspecialchars($f['destinationAirport']) ?><br>
                                Date: <?= htmlspecialchars($f['flightDate']) ?><br>
                                Departure: <?= htmlspecialchars($f['segmentsDepartureTimeRaw']) ?> | Arrival: <?= htmlspecialchars($f['segmentsArrivalTimeRaw']) ?><br>
                                Duration: <?= htmlspecialchars($f['travelDuration']) ?><br>
                                Fare: $<?= number_format($f['totalFare'] ?? 0, 2) ?><br>
                                Seats Remaining: <?= $f['seatsRemaining'] ?? 0 ?><br>

                                <form method="POST" action="book_flight.php">
                                    <?php foreach ($f as $key => $val): ?>
                                        <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($val) ?>">
                                    <?php endforeach; ?>
                                    <button type="submit" class="btn btn-book btn-sm mt-2">Book Now</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No flights available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
