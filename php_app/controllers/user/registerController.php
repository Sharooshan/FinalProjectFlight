<?php
// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form values
    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $address = trim($_POST['address']);
    $dob = $_POST['dob'];

    // // Basic password match validation
    // if ($password !== $confirmPassword) {
    //     $_SESSION['error'] = "Passwords do not match!";
    //     header("Location: ../../../public/register.php");
    //     exit();
    // }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Email already registered!";
        header("Location: ../../../public/register.php");
        exit();
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (fullName, email, mobile, password, address, dob) 
                            VALUES (:fullName, :email, :mobile, :password, :address, :dob)");
    $result = $stmt->execute([
        'fullName' => $fullName,
        'email' => $email,
        'mobile' => $mobile,
        'password' => $hashedPassword,
        'address' => $address,
        'dob' => $dob
    ]);

    if ($result) {
        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: ../../../public/login.php");
        exit();
    } else {
        $_SESSION['error'] = "Something went wrong. Please try again.";
        header("Location: ../../../public/register.php");
        exit();
    }
} else {
    header("Location: ../../../public/register.php");
    exit();
}
?>
