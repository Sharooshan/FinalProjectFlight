<?php
session_start();
$pdo = include '../../config/db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $review_text = trim($_POST['review_text']);
    $rating = (int)$_POST['rating'];

    if ($review_text === '' || $rating < 1 || $rating > 5) {
        $error = "Please enter a valid review and rating (1–5).";
    } else {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, review_text, rating) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $review_text, $rating]);
        $success = "✅ Your review has been submitted!";
    }
}

// Fetch all user reviews
$stmtReviews = $pdo->prepare("
    SELECT r.*, u.fullName 
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
");
$stmtReviews->execute();
$reviews = $stmtReviews->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Reviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col-md-8 p-4">
            <h3>Submit a Review</h3>

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if(!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" class="mb-4">
                <div class="mb-3">
                    <label class="form-label">Your Review</label>
                    <textarea name="review_text" class="form-control" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Rating</label>
                    <select name="rating" class="form-control" required>
                        <option value="">Select Stars</option>
                        <?php for($i=1;$i<=5;$i++): ?>
                            <option value="<?= $i ?>"><?= $i ?> Star<?= $i>1?'s':'' ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Submit Review</button>
            </form>

            <hr>
            <h4>All Reviews</h4>
            <?php if($reviews): ?>
                <ul class="list-group">
                    <?php foreach($reviews as $r): ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($r['fullName']) ?></strong> 
                            rated <strong><?= $r['rating'] ?> ⭐</strong><br>
                            <?= htmlspecialchars($r['review_text']) ?><br>
                            <small class="text-muted">Submitted on <?= $r['created_at'] ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">No reviews yet.</p>
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
