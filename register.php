<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IntelliFlight - Register</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f4f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .register-wrapper {
            min-height: 95vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-card {
            display: flex;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 12px 36px rgba(0,0,0,0.15);
            max-width: 1000px;
            width: 90%;
            min-height: 650px;
        }

        .register-left {
            flex: 1;
            background: url('https://images.unsplash.com/photo-1519125323398-675f0ddb6308?auto=format&fit=crop&w=1200&q=80') center/cover no-repeat;
            min-width: 350px;
        }

        .register-right {
            flex: 1;
            background: #ffffff;
            padding: 60px 50px;
            min-width: 350px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .register-header {
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
            .register-card {
                flex-direction: column;
            }
            .register-left {
                height: 250px;
                min-width: 100%;
            }
            .register-right {
                padding: 40px 30px;
                min-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container register-wrapper">
    <div class="register-card">

        <!-- Left Side: Image -->
        <div class="register-left"></div>

        <!-- Right Side: Form -->
        <div class="register-right">
            <div class="register-header">✈️ IntelliFlight</div>

            <form action="../php_app/controllers/user/registerController.php" method="POST">

                <!-- Full Name -->
                <div class="form-group">
                    <span class="input-icon">👤</span>
                    <input type="text" class="form-control" id="fullName" name="fullName" placeholder="Full Name" required>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <span class="input-icon">📧</span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required>
                </div>

                <!-- Mobile Number -->
                <div class="form-group">
                    <span class="input-icon">📱</span>
                    <input type="tel" class="form-control" id="mobile" name="mobile" placeholder="Mobile Number" pattern="[0-9]{10}" required>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <span class="input-icon">🔒</span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                </div>

                <!-- Address -->
                <div class="form-group">
                    <span class="input-icon">🏠</span>
                    <textarea class="form-control" id="address" name="address" rows="2" placeholder="Address (Optional)"></textarea>
                </div>

                <!-- Date of Birth -->
                <div class="form-group">
                    <span class="input-icon">🎂</span>
                    <input type="date" class="form-control" id="dob" name="dob" placeholder="Date of Birth (Optional)">
                </div>

                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>

            <p class="mt-4 text-center">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
