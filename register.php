<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('config/db.php');

$success = "";
$error = "";

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));
    $role = mysqli_real_escape_string($conn, trim($_POST['role']));
    $department = mysqli_real_escape_string($conn, trim($_POST['department']));

    if ($name == "" || $email == "" || $password == "" || $role == "") {
        $error = "Please fill in all required fields.";
    } else {
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

        if (mysqli_num_rows($check) > 0) {
            $error = "This email is already registered.";
        } else {
            $sql = "INSERT INTO users (name, email, password_hash, role, department, is_active)
                    VALUES ('$name', '$email', '$password', '$role', '$department', 1)";

            if (mysqli_query($conn, $sql)) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Peer Supporting Management</title>
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

        .register-wrapper {
            width: 100%;
            max-width: 980px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
        }

        .register-left {
            padding: 60px 45px;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(255, 255, 255, 0.08);
        }

        .register-left h1 {
            font-size: 34px;
            line-height: 1.2;
            margin-bottom: 18px;
        }

        .register-left p {
            font-size: 15px;
            opacity: 0.92;
            line-height: 1.8;
        }

        .register-right {
            background: #ffffff;
            padding: 50px 38px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-card {
            width: 100%;
            max-width: 390px;
        }

        .register-card h2 {
            font-size: 30px;
            color: #111827;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 24px;
        }

        .success-msg {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 18px;
            font-size: 14px;
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
            margin-bottom: 16px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
        }

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #d1d5db;
            border-radius: 14px;
            font-size: 15px;
            outline: none;
            transition: 0.3s ease;
            background: #f9fafb;
        }

        .input-group input:focus,
        .input-group select:focus {
            border-color: #6366f1;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }

        .register-btn {
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
            margin-top: 6px;
            box-shadow: 0 10px 24px rgba(79, 70, 229, 0.28);
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(79, 70, 229, 0.35);
        }

        .footer-text {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            color: #6b7280;
        }

        .footer-text a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        @media (max-width: 860px) {
            .register-wrapper {
                grid-template-columns: 1fr;
            }

            .register-left {
                padding: 35px 30px;
            }

            .register-right {
                padding: 35px 25px;
            }

            .register-left h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="register-wrapper">
        <div class="register-left">
            <h1>Create Your Account</h1>
            <p>
                Join the Peer Supporting Management System as a peer or therapist.
                Create your profile and access your role-based dashboard securely.
            </p>
        </div>

        <div class="register-right">
            <div class="register-card">
                <h2>Register</h2>
                <p class="subtitle">Fill in your details to create a new account</p>

                <?php if ($success) : ?>
                    <div class="success-msg"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if ($error) : ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                    </div>

                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="text" id="password" name="password" placeholder="Create a password" required>
                    </div>

                    <div class="input-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" placeholder="Enter department">
                    </div>

                    <div class="input-group">
                        <label for="role">Select Role</label>
                        <select id="role" name="role" required>
                            <option value="">Choose role</option>
                            <option value="peer">Peer</option>
                            <option value="therapist">Therapist</option>
                        </select>
                    </div>

                    <button type="submit" name="register" class="register-btn">Create Account</button>
                </form>

                <p class="footer-text">
                    Already have an account? <a href="login.php">Login</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>