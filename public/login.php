<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IntelliFlight - Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f4f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-wrapper {
            min-height: 95vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            display: flex;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 12px 36px rgba(0,0,0,0.15);
            max-width: 1000px;
            width: 90%;
            min-height: 600px;
        }

        .login-left {
            flex: 1;
            background: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80') center/cover no-repeat;
            min-width: 350px;
        }

        .login-right {
            flex: 1;
            background: #ffffff;
            padding: 60px 50px;
            min-width: 350px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            font-weight: 900;
            font-size: 2.5rem;
            color: #0b5fff;
            text-align: center;
            margin-bottom: 35px;
        }

        .form-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.5rem;
            color: #0b5fff;
        }

        .form-control {
            padding-left: 45px;
            font-size: 1.1rem;
            height: 55px;
            border-radius: 10px;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(11,95,255,.25);
            border-color: #0b5fff;
        }

        .btn-primary {
            background: #0b5fff;
            border: none;
            padding: 15px;
            font-size: 1.1rem;
            border-radius: 10px;
        }

        .btn-primary:hover {
            background: #094acc;
        }

        p.text-center a {
            font-weight: 600;
            color: #0b5fff;
        }

        @media(max-width: 992px) {
            .login-card {
                flex-direction: column;
            }
            .login-left {
                height: 250px;
                min-width: 100%;
            }
            .login-right {
                padding: 40px 30px;
                min-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container login-wrapper">
    <div class="login-card">

        <!-- Left Side: Image -->
        <div class="login-left"></div>

        <!-- Right Side: Form -->
        <div class="login-right">
            <div class="login-header">✈️ IntelliFlight</div>

            <!-- Display session messages -->
            <?php
            session_start();
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            ?>

            <form action="../php_app/controllers/user/loginController.php" method="POST">

                <!-- Email -->
                <div class="form-group">
                    <span class="input-icon">📧</span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <span class="input-icon">🔒</span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>

            <p class="mt-4 text-center">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
