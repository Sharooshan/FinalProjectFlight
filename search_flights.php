<?php
session_start();
include '../../config/db.php';

$flights = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $origin = $_POST['origin'] ?? '';
    $destination = $_POST['destination'] ?? '';

    // Fetch all flights for the route (ignore date)
    $stmt = $conn->prepare("SELECT * FROM flights WHERE startingAirport=:origin AND destinationAirport=:destination");
    $stmt->execute([
        'origin' => $origin,
        'destination' => $destination
    ]);

    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Send flights to Flask API for predicted fare
    foreach ($flights as &$flight) {
        // Calculate elapsedDays based on the stored flightDate in DB
        $flightDateFromDB = $flight['flightDate'] ?? date('Y-m-d');
        $elapsedDays = max(0, round((strtotime($flightDateFromDB) - time()) / (60*60*24)));

        $ch = curl_init("http://127.0.0.1:5000/predict");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POST, true);

        $payload = [
            "elapsedDays" => $elapsedDays,
            "isBasicEconomy" => (int)$flight['isBasicEconomy'],
            "isRefundable" => (int)$flight['isRefundable'],
            "isNonStop" => (int)$flight['isNonStop'],
            "totalTravelDistance" => (int)$flight['totalTravelDistance'],
            "travelDuration" => $flight['travelDuration']
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response !== false) {
            $pred = json_decode($response, true);
            $flight['predictedFare'] = $pred['predicted_fare'] ?? null;
        } else {
            $flight['predictedFare'] = null;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Flights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h3>Flight Results</h3>

    <?php if (empty($flights)) : ?>
        <div class="alert alert-warning">No flights found for your search criteria.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($flights as $flight): ?>
                <div class="col-md-6 mb-3">
                    <div class="card p-3">
                        <h5><?= htmlspecialchars($flight['segmentsAirlineName']) ?> (<?= htmlspecialchars($flight['segmentsAirlineCode']) ?>)</h5>
                        <p>
                            <?= htmlspecialchars($flight['startingAirport']) ?> → <?= htmlspecialchars($flight['destinationAirport']) ?><br>
                            Departure: <?= htmlspecialchars($flight['segmentsDepartureTimeRaw']) ?><br>
                            Arrival: <?= htmlspecialchars($flight['segmentsArrivalTimeRaw']) ?><br>
                            Duration: <?= htmlspecialchars($flight['travelDuration']) ?><br>
                            Non-stop: <?= $flight['isNonStop'] ? 'Yes' : 'No' ?>
                        </p>
                        <h6>Predicted Fare: 
                            <?= $flight['predictedFare'] !== null ? '$'.number_format($flight['predictedFare'], 2) : 'N/A' ?>
                        </h6>
                        <form method="POST" action="book_flight.php">
                            <input type="hidden" name="legId" value="<?= htmlspecialchars($flight['legId']) ?>">
                            <input type="hidden" name="predictedFare" value="<?= htmlspecialchars($flight['predictedFare']) ?>">
                            <button type="submit" class="btn btn-primary">Book Now</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
