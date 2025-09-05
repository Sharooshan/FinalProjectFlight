<?php
session_start();
require_once __DIR__ . '/../../config/db.php'; // adjust path to your db.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // Fetch admin record from database
        $stmt = $conn->prepare("SELECT id, username, email, password FROM admins WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() === 1) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify plain password
            if ($password === $admin['password']) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['username'];
                header("Location: ../..//views/user/admin/dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Incorrect password!";
                header("Location: ../../../public/admin_login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Email not registered!";
            header("Location: ../../../public/admin_login.php");
            exit();
        }

    } catch(PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    header("Location: ../../../public/admin_login.php");
    exit();
}
?>
