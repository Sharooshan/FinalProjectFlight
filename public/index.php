<?php
// db.php: Your database connection
$pdo = new PDO('mysql:host=127.0.0.1;dbname=finalproject', 'root', '');

// Fetch average totalFare per month (last 6 months)
$query = $pdo->query("
    SELECT DATE_FORMAT(flightDate, '%Y-%m') AS month, AVG(totalFare) AS avgFare
    FROM flightsfuture
    WHERE flightDate >= CURDATE() - INTERVAL 6 MONTH
    GROUP BY month
    ORDER BY month ASC
");

$priceData = $query->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$values = [];

foreach ($priceData as $row) {
    $labels[] = $row['month'];
    $values[] = round($row['avgFare'], 2);
}

$labelsJS = json_encode($labels);
$valuesJS = json_encode($values);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>IntelliFlight - Home</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include 'navbar.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section d-flex align-items-center justify-content-center position-relative"
        style="background-color: #f0f4f8; min-height: 100vh; width: 100%; overflow: hidden;">
        <div class="container-fluid px-0 position-absolute top-0 start-0 end-0 bottom-0">
            <img src="https://images.pexels.com/photos/3775121/pexels-photo-3775121.jpeg"
                alt="Happy passengers in airplane"
                class="img-fluid w-100 h-100 object-fit-cover">
        </div>
        <div class="container text-white position-relative z-index-2 py-5">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-2 fw-bold">AI Driven Flight Booking and Fare Prediction System</h1>
                    <p class="lead mt-3 fs-4">Smart, fast, and reliable flight booking with intelligent fare predictions using AI.</p>
                    <a href="register.php" class="btn btn-primary btn-lg mt-3 shadow-lg px-4 py-2">
                        Get Started <span class="ms-2">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Travel Services Section -->
    <style>
        .ts-wrap {
            --ts-gap: 18px;
            --ts-card-bg: #fff;
            --ts-card-r: 18px;
            --ts-shadow: 0 8px 24px rgba(0, 0, 0, .08);
            --ts-text: #1d1d1f;
            --ts-muted: #666;
            --ts-accent: #0b5fff;
            --ts-border: #ececec;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--ts-text);
        }

        .ts-header {
            text-align: center;
            margin: 0 auto 28px;
            max-width: 800px;
            padding: 0 12px;
        }

        .ts-title {
            font-weight: 800;
            letter-spacing: -.02em;
            line-height: 1.1;
            margin: 0;
            font-size: 2rem;
        }

        .ts-sub {
            color: var(--ts-muted);
            margin: 8px 0 0;
            font-size: 1rem;
        }

        .ts-grid-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 12px 40px;
        }

        .ts-grid {
            display: grid;
            gap: var(--ts-gap);
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }

        .ts-card {
            background: var(--ts-card-bg);
            border: 1px solid var(--ts-border);
            border-radius: var(--ts-card-r);
            overflow: hidden;
            box-shadow: var(--ts-shadow);
            display: flex;
            flex-direction: column;
            transition: transform .2s ease, box-shadow .2s ease;
            text-align: center;
            padding: 30px 20px;
        }

        .ts-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, .12);
        }

        .ts-icon {
            font-size: 48px;
            margin-bottom: 16px;
            color: var(--ts-accent);
        }

        .ts-h3 {
            margin: 6px 0;
            font-size: 20px;
            font-weight: 700;
        }

        .ts-desc {
            color: var(--ts-muted);
            font-size: 14px;
            line-height: 1.5;
            margin: 0 0 16px;
        }

        .ts-link {
            display: inline-block;
            font-weight: 600;
            font-size: 14px;
            color: var(--ts-accent);
            text-decoration: none;
            margin-top: auto;
        }

        .ts-link:hover {
            text-decoration: underline;
        }
    </style>

    <section class="ts-wrap" aria-label="Travel Services">
        <div class="ts-header">
            <h2 class="ts-title">Integrated Travel Services</h2>
            <p class="ts-sub">Everything you need for your journey, managed in one smart platform.</p>
        </div>

        <div class="ts-grid-wrap">
            <div class="ts-grid">
                <article class="ts-card">
                    <div class="ts-icon">🏨</div>
                    <h3 class="ts-h3">Hotels</h3>
                    <p class="ts-desc">Book top hotels with the best rates and trusted reviews at your destination.</p><a href="#" class="ts-link">Explore Hotels →</a>
                </article>
                <article class="ts-card">
                    <div class="ts-icon">🚖</div>
                    <h3 class="ts-h3">Cabs</h3>
                    <p class="ts-desc">Reliable local transportation with cab bookings made easy and safe.</p><a href="#" class="ts-link">Book a Cab →</a>
                </article>
                <article class="ts-card">
                    <div class="ts-icon">🍽️</div>
                    <h3 class="ts-h3">Food</h3>
                    <p class="ts-desc">Discover top restaurants and order delicious food directly from our app.</p><a href="#" class="ts-link">Find Food →</a>
                </article>
                <article class="ts-card">
                    <div class="ts-icon">🛡️</div>
                    <h3 class="ts-h3">Insurance</h3>
                    <p class="ts-desc">Travel with peace of mind using recommended travel insurance services.</p><a href="#" class="ts-link">Get Insurance →</a>
                </article>
            </div>
        </div>
    </section>

    <!-- FAQ Section (unchanged) -->
    <!-- Q&A Accordion Section -->
    <div class="container-fluid my-5 px-5">
        <h2 class="text-center mb-4">Frequently Asked Questions</h2>
        <div class="accordion" id="faqAccordion">

            <div class="accordion-item">
                <h2 class="accordion-header" id="faq1">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#answer1" aria-expanded="true" aria-controls="answer1">
                        How can I book a hotel through the app?
                    </button>
                </h2>
                <div id="answer1" class="accordion-collapse collapse show" aria-labelledby="faq1" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        You can browse hotels in your selected destination and book directly with a few clicks. Payment options are also integrated for your convenience.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="faq2">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer2" aria-expanded="false" aria-controls="answer2">
                        Can I book cabs in advance?
                    </button>
                </h2>
                <div id="answer2" class="accordion-collapse collapse" aria-labelledby="faq2" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes, cab bookings can be scheduled in advance, ensuring reliable transportation at your preferred time.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="faq3">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer3" aria-expanded="false" aria-controls="answer3">
                        Is food delivery available everywhere?
                    </button>
                </h2>
                <div id="answer3" class="accordion-collapse collapse" aria-labelledby="faq3" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Food delivery availability depends on the location. Most major cities and tourist destinations are covered.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="faq4">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer4" aria-expanded="false" aria-controls="answer4">
                        Do I need travel insurance for every trip?
                    </button>
                </h2>
                <div id="answer4" class="accordion-collapse collapse" aria-labelledby="faq4" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        While not mandatory, we highly recommend travel insurance for safety, especially for international trips.
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Intelligent Travel Planning Section -->
    <div class="container my-5">
        <h2 class="text-center mb-4">Intelligent Travel Planning</h2>
        <div class="row g-4">
            <!-- Price Trends Chart -->
            <div class="col-md-4">
                <div class="card shadow-sm text-center p-3">
                    <h5 class="card-title mb-3">Price Trends</h5>
                    <div style="height:250px;">
                        <canvas id="priceChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Customers Chart -->
            <div class="col-md-4">
                <div class="card shadow-sm text-center p-3">
                    <h5 class="card-title mb-3">Active Customers</h5>
                    <div style="height:250px;">
                        <canvas id="customersChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Services Usage Chart -->
            <div class="col-md-4">
                <div class="card shadow-sm text-center p-3">
                    <h5 class="card-title mb-3">Services Usage</h5>
                    <div style="height:250px;">
                        <canvas id="servicesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const priceCtx = document.getElementById('priceChart').getContext('2d');
        new Chart(priceCtx, {
            type: 'line',
            data: {
                labels: <?= $labelsJS ?>,
                datasets: [{
                    label: 'Average Fare ($)',
                    data: <?= $valuesJS ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.2)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: '#3b82f6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });

        // Customers Chart (keep dummy or later pull from DB)
        const customersCtx = document.getElementById('customersChart').getContext('2d');
        const gradient = customersCtx.createLinearGradient(0, 0, 0, 200);
        gradient.addColorStop(0, '#10b981');
        gradient.addColorStop(1, '#6ee7b7');
        new Chart(customersCtx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Users',
                    data: [50, 75, 60, 90, 120, 150, 100],
                    backgroundColor: gradient,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Services Usage Chart (keep dummy or later pull from DB)
        const servicesCtx = document.getElementById('servicesChart').getContext('2d');
        new Chart(servicesCtx, {
            type: 'doughnut',
            data: {
                labels: ['Hotels', 'Cabs', 'Food', 'Insurance'],
                datasets: [{
                    label: 'Service Usage',
                    data: [40, 25, 20, 15],
                    backgroundColor: ['#6366f1', '#f59e0b', '#ef4444', '#10b981'],
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

    <?php
    // Fetch all reviews with user names and ratings
    $stmtReviews = $pdo->prepare("
    SELECT r.*, u.fullName 
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
");
    $stmtReviews->execute();
    $reviews = $stmtReviews->fetchAll(PDO::FETCH_ASSOC);

    // Split reviews into chunks of 3 per slide
    $reviewChunks = array_chunk($reviews, 3);
    ?>

    <?php if ($reviews): ?>
        <div class="container my-5">
            <h2 class="text-center mb-4" style="color:#0b5fff; font-weight:600;">⭐ User Reviews</h2>
            <div id="reviewsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000" data-bs-pause="hover">
                <div class="carousel-inner">

                    <?php foreach ($reviewChunks as $i => $chunk): ?>
                        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                            <div class="row justify-content-center">
                                <?php foreach ($chunk as $r): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card shadow-sm h-100" style="border-radius:15px;">
                                            <div class="card-body text-center p-4 d-flex flex-column">
                                                <h5 class="card-title mb-2"><?= htmlspecialchars($r['fullName']) ?></h5>
                                                <p class="text-warning mb-2">
                                                    <?= str_repeat('⭐', $r['rating']) ?><?= str_repeat('☆', 5 - $r['rating']) ?>
                                                </p>
                                                <p class="card-text fst-italic flex-grow-1">"<?= htmlspecialchars($r['review_text']) ?>"</p>
                                                <small class="text-muted"><?= date('M d, Y', strtotime($r['created_at'])) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    <?php endif; ?>
    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row">

                <!-- About -->
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold text-primary">IntelliFlight</h5>
                    <p class="small text-light">AI-driven Flight Booking & Travel Management System. Smart, fast, and reliable for all your journeys.</p>
                </div>

                <!-- Quick Links -->
                <div class="col-md-3 mb-4">
                    <h6 class="fw-bold text-primary">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-light text-decoration-none">Home</a></li>
                        <li><a href="bookings.php" class="text-light text-decoration-none">My Bookings</a></li>
                        <li><a href="Review.php" class="text-light text-decoration-none">Reviews</a></li>
                        <li><a href="contact.php" class="text-light text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="col-md-3 mb-4">
                    <h6 class="fw-bold text-primary">Contact</h6>
                    <p class="small mb-1">📍 123 Travel St, Colombo, Sri Lanka</p>
                    <p class="small mb-1">📞 +94 11 123 4567</p>
                    <p class="small mb-1">✉️ support@intelliflight.com</p>
                </div>

                <!-- Social -->
                <div class="col-md-2 mb-4">
                    <h6 class="fw-bold text-primary">Follow Us</h6>
                    <div class="d-flex gap-2 mt-2">
                        <a href="#" class="text-light fs-4"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light fs-4"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-light fs-4"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light fs-4"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>

            </div>

            <hr class="bg-light">

            <div class="text-center small">
                &copy; <?= date('Y') ?> IntelliFlight. All Rights Reserved.
            </div>
        </div>
    </footer>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>