<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../../public/admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - IntelliFlight</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../../public/css/style.css">
<style>
    body {
            display: flex;
            margin: 0;
            min-height: 100vh;
            background: #f8f9fa;
        }
        .sidebar {
            width: 220px;
            background-color: #343a40;
            color: white;
            padding: 20px 0;
            flex-shrink: 0;
        }
        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
        }
        .content {
            flex: 1;
            padding: 20px;
            overflow-x: auto;
        }
</style>
</head>
<body>

<div class="d-flex">
    <!-- Include external admin sidebar -->
    <?php include __DIR__ . '/sidebar_admin.php'; ?>
    

    <!-- Main Content -->
    <div class="main-content">
        <div id="dashboard">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</h2>
            <p>This is your admin dashboard. Use the sidebar to manage flights, users, reports, and analytics.</p>
        </div>

        <div id="flights" style="display:none;">
            <h2>Manage Flights</h2>
            <p>Flight management interface will appear here.</p>
        </div>

        <div id="users" style="display:none;">
            <h2>User Management</h2>
            <p>User management interface will appear here.</p>
        </div>

        <div id="reports" style="display:none;">
            <h2>Reports</h2>
            <p>Reports and summaries will appear here.</p>
        </div>

        <div id="analytics" style="display:none;">
            <h2>Analytics</h2>
            <p>Analytics dashboards will appear here.</p>
        </div>
    </div>
</div>

<script>
function showSection(sectionId) {
    const sections = ['dashboard','flights','users','reports','analytics'];
    sections.forEach(id => {
        document.getElementById(id).style.display = (id === sectionId) ? 'block' : 'none';
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
