<?php
// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<style>
    /* Sidebar */
    .sidebar {
        width: 250px;
        background: #ffffff;
        padding: 20px;
        height: 100vh;
        position: sticky;
        top: 0;
        left: 0;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
    }

    /* Dashboard Header with Stickers */
    .dashboard-header {
        font-weight: 900;
        font-size: 1.8rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: linear-gradient(90deg, #0b5fff 0%, #10b981 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.15);
    }

    /* Sticker bounce */
    .dashboard-header .sticker {
        font-size: 1.6rem;
        animation: bounce 2s infinite alternate;
    }

    @keyframes bounce {
        0% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-6px);
        }

        100% {
            transform: translateY(0);
        }
    }

    /* Navigation Links */
    .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        margin-bottom: 6px;
        font-weight: 500;
        border-radius: 8px;
        color: #1f2937;
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
    }

    /* Active link styling */
    .nav-link.active {
        background: #0b5fff;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(11, 95, 255, 0.3);
    }

    /* Hover effects */
    .nav-link:hover {
        background: linear-gradient(90deg, #0b5fff, #10b981);
        color: #fff;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Emoji icons for links */
    .nav-link::before {
        content: attr(data-icon);
        font-size: 1.3rem;
        margin-right: 10px;
    }

    /* Logout button */
    .nav-link.logout {
        margin-top: auto;
        background: #ef4444;
        color: #fff !important;
        border-radius: 12px;
        text-align: center;
        padding: 10px 0;
        transition: all 0.3s ease;
    }

    .nav-link.logout:hover {
        background: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    /* Creative User Greeting */
    .user-greeting {
        margin-top: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-weight: 700;
        font-size: 1.1rem;
        background: linear-gradient(90deg, #0b5fff, #10b981);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    }

    /* Greeting stickers wave */
    .user-greeting .sticker {
        font-size: 1.5rem;
        animation: wave 2s infinite;
    }

    @keyframes wave {

        0%,
        100% {
            transform: rotate(0deg);
        }

        25% {
            transform: rotate(15deg);
        }

        50% {
            transform: rotate(-10deg);
        }

        75% {
            transform: rotate(10deg);
        }
    }
</style>
<div class="sidebar d-flex flex-column">
    <!-- Dashboard Header -->
    <a href="user_landing.php" class="dashboard-header mb-3">
        <span class="sticker">🌟</span>
        <span>Dashboard</span>
        <span class="sticker">✈️</span>
    </a>
    <hr>

    <!-- Navigation Links -->
    <ul class="nav flex-column mb-auto">
        <li><a href="user_landing.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'user_landing.php' ? 'active' : ''; ?>" data-icon="🏠">Home</a></li>
        <li><a href="All_flights.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'All_flights.php' ? 'active' : ''; ?>" data-icon="✈️">All Flights</a></li>
        <li><a href="my_bookings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my_bookings.php' ? 'active' : ''; ?>" data-icon="📝">My Bookings</a></li>

        <li><a href="Review.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'review.php' ? 'active' : ''; ?>" data-icon="⭐">reviews</a></li>
        <!-- Add this inside the <ul class="nav flex-column mb-auto"> list -->
        <li>
            <a href="insurance.php"
                class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'insurance.php' ? 'active' : ''; ?>"
                data-icon="🛡️">Insurance</a>
        </li>

    </ul>

    <!-- User Greeting and Logout at Bottom -->
    <div class="mt-auto">
        <div class="user-greeting mb-2">
            <span class="sticker">👋</span>
            <span>Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <span class="sticker">🛫</span>
        </div>
        <a href="logout.php" class="nav-link logout" data-icon="🚪">Logout</a>
    </div>
</div>