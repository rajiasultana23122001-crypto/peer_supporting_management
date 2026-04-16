<?php
session_start();
include('config/db.php');

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email' AND password_hash='$password' AND is_active=1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } elseif ($user['role'] == 'therapist') {
            header("Location: therapist/dashboard.php");
        } else {
            header("Location: peer/dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Peer Supporting Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #4f46e5, #7c3aed, #06b6d4);
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 950px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
        }

        .login-left {
            padding: 60px 45px;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(255, 255, 255, 0.08);
        }

        .login-left h1 {
            font-size: 36px;
            line-height: 1.2;
            margin-bottom: 18px;
        }

        .login-left p {
            font-size: 15px;
            opacity: 0.9;
            line-height: 1.8;
        }

        .login-right {
            background: #ffffff;
            padding: 55px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 380px;
        }

        .login-card h2 {
            font-size: 30px;
            color: #111827;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 28px;
        }

        .error-msg {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .input-group {
            margin-bottom: 18px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
        }

        .input-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #d1d5db;
            border-radius: 14px;
            font-size: 15px;
            outline: none;
            transition: 0.3s ease;
            background: #f9fafb;
        }

        .input-group input:focus {
            border-color: #6366f1;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }

        .login-btn {
            width: 100%;
            border: none;
            padding: 14px;
            border-radius: 14px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s ease;
            margin-top: 8px;
            box-shadow: 0 10px 24px rgba(79, 70, 229, 0.28);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(79, 70, 229, 0.35);
        }

        .footer-text {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            color: #9ca3af;
        }

        @media (max-width: 860px) {
            .login-wrapper {
                grid-template-columns: 1fr;
            }

            .login-left {
                padding: 35px 30px;
            }

            .login-right {
                padding: 35px 25px;
            }

            .login-left h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-left">
            <h1>Peer Supporting Management System</h1>
            <p>
                Welcome back! লগইন করে সহজে students, cases, activities
                এবং support workflow manage করো এক জায়গা থেকে।
            </p>
        </div>

        <div class="login-right">
            <div class="login-card">
                <h2>Sign In</h2>
                <p class="subtitle">Please enter your account details</p>

                <?php if (isset($error)) : ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>

                    <button type="submit" name="login" class="login-btn">Login</button>
                </form>

                <p class="footer-text">Secure access for Admin, Therapist & Peer</p>
            </div>
        </div>
    </div>
</body>
</html>