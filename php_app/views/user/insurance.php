<?php
session_start();
$pdo = include '../../config/db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) die("Please log in first.");

// Available plans
$plans = [
    'Monthly' => 50,
    '6 Months' => 250,
    'Yearly' => 450,
    '2 Years' => 800
];

$successMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan = $_POST['plan'] ?? '';
    if (!isset($plans[$plan])) die("Invalid plan selection.");

    $price = $plans[$plan];
    $start_date = date('Y-m-d');

    // Calculate end date based on plan
    switch ($plan) {
        case 'Monthly':
            $end_date = date('Y-m-d', strtotime('+1 month'));
            break;
        case '6 Months':
            $end_date = date('Y-m-d', strtotime('+6 months'));
            break;
        case 'Yearly':
            $end_date = date('Y-m-d', strtotime('+1 year'));
            break;
        case '2 Years':
            $end_date = date('Y-m-d', strtotime('+2 years'));
            break;
    }

    // Generate promo code
    $promo_code = strtoupper(substr($plan,0,1) . bin2hex(random_bytes(3)));

    // Save in user_insurance
    $stmt = $pdo->prepare("INSERT INTO user_insurance (user_id, plan_type, start_date, end_date, price, promo_code) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $plan, $start_date, $end_date, $price, $promo_code]);

    // Optionally save promo info
    $stmtPromo = $pdo->prepare("INSERT INTO insurance_promos (code, discount, plan_type, valid_until) VALUES (?, ?, ?, ?)");
    $stmtPromo->execute([$promo_code, 10, $plan, date('Y-m-d', strtotime('+1 year'))]); // 10% discount promo

    $successMessage = "✅ You purchased <strong>$plan</strong> insurance! Your promo code: <strong>$promo_code</strong>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insurance Purchase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <!-- Left Sidebar -->
        <div class="col-md-2">
            <?php include 'sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-8 p-4">
            <h3>Buy Insurance Plan</h3>
            <?php if($successMessage): ?>
                <div class="alert alert-success"><?= $successMessage ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Select Plan</label>
                    <select name="plan" class="form-control" required>
                        <?php foreach($plans as $planName => $price): ?>
                            <option value="<?= $planName ?>"><?= $planName ?> - $<?= $price ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Buy Insurance</button>
            </form>
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
