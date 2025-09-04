<?php
// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


require_once __DIR__ . '/../../config/db.php';

// Ensure POST values are set
if (!isset($_POST['email'], $_POST['password'])) {
    $_SESSION['error'] = "Please fill in both email and password!";
    header("Location: ../../../public/login.php");
    exit();
}

$email = trim($_POST['email']);
$password = $_POST['password'];

try {
    // Prepare statement to fetch user
    $stmt = $conn->prepare("SELECT id, fullName, password FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['fullName'];
            $_SESSION['success'] = "Welcome, " . $user['fullName'] . "!";
            header("Location: ../../views/user/user_landing.php");
            exit();
        } else {
            // Incorrect password
            $_SESSION['error'] = "Incorrect password!";
            header("Location: ../../../public/login.php");
            exit();
        }
    } else {
        // Email not found
        $_SESSION['error'] = "Email not registered!";
        header("Location: ../../../public/login.php");
        exit();
    }

} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
