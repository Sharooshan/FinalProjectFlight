<?php
// session_start();
?>

<style>
/* Sidebar Styling */
.sidebar {
    width: 250px;
    height: 100vh;
    background: #1f2937; /* dark professional tone */
    color: #f9fafb;
    display: flex;
    flex-direction: column;
    padding: 20px 0;
    box-shadow: 2px 0 8px rgba(0,0,0,0.1);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Sidebar Header */
.sidebar h4 {
    text-align: center;
    font-weight: 700;
    font-size: 1.5rem;
    margin-bottom: 1rem;
    letter-spacing: 1px;
    color: #0b5fff; /* blue accent */
}

/* Sidebar Links */
.sidebar a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #f9fafb;
    text-decoration: none;
    font-weight: 500;
    font-size: 1rem;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

/* Link Hover Effect */
.sidebar a:hover {
    background: #0b5fff;
    color: #ffffff;
    border-left: 4px solid #10b981;
    padding-left: 24px;
}

/* Active Link */
.sidebar a.active {
    background: #0b5fff;
    color: #ffffff;
    border-left: 4px solid #10b981;
    font-weight: 600;
}

/* Sidebar HR */
.sidebar hr {
    border-color: rgba(255,255,255,0.2);
    margin: 15px 0;
}

/* Icons for links */
.sidebar a::before {
    content: attr(data-icon);
    font-family: 'Segoe UI Emoji';
    margin-right: 10px;
    font-size: 1.2rem;
}

/* Logout styling */
.sidebar a.logout {
    margin-top: auto;
    background: #ef4444;
    color: #ffffff !important;
    text-align: center;
    border-radius: 0 20px 20px 0;
    transition: background 0.3s ease;
}

.sidebar a.logout:hover {
    background: #dc2626;
    padding-left: 20px;
}
</style>

<div class="sidebar">
    <h4>Admin Panel</h4>
    <a href="#" class="active" data-icon="🏠" onclick="showSection('dashboard')">Dashboard</a>
    <a href="#" data-icon="✈️" onclick="showSection('flights')">Manage Flights</a>
    <a href="userManagement.php" data-icon="👤" onclick="showSection('users')">User Management</a>
    <a href="userBookingManagement.php" data-icon="📝" onclick="showSection('usersBooking')">User Booking Management</a>
     <!-- ✅ New Food Management Button -->
    <a href="foodManagement.php" data-icon="🍔" onclick="showSection('food')">Food Management</a>
    <a href="hotelManagement.php" data-icon="🏨" onclick="showSection('hotel')">Hotel Management</a>
    <a href="cabManagement.php" data-icon="🚖" onclick="showSection('cab')">Cab Management</a>
    <a href="insuranceManagement.php" data-icon="🛡️" onclick="showSection('insurance')">Insurance Management</a>
    <a href="#" data-icon="📊" onclick="showSection('reports')">Reports</a>
    <a href="#" data-icon="📈" onclick="showSection('analytics')">Analytics</a>
    <hr>
    <a href="../../../php_app/controllers/admin/logout.php" class="logout" data-icon="🚪">Logout</a>
</div>
