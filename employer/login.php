<?php
session_start();
include '../db.php';

$message = ''; // To display success or error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Fetch user from database
    $sql = "SELECT * FROM users WHERE email = '$email' AND user_type = 'employer'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            // Check if the user is verified
            if ($user['status'] === 'verified') {
                // Start a session and redirect to the employer dashboard
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_type'] = $user['user_type'];
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "account_not_verified";
            }
        } else {
            $message = "invalid_credentials";
        }
    } else {
        $message = "invalid_credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Login | GoSeekr</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0a66c2;
            --secondary-color: #057642;
            --background-color: #f3f2ef;
            --text-color: #333333;
            --light-gray: #eaeaea;
        }
        
        body {
            background-color: var(--background-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
        }
        
        .login-container {
            max-width: 450px;
            margin: 60px auto;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--light-gray);
            padding: 25px 30px;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .logo {
            font-size: 26px;
            font-weight: bold;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        
        .logo i {
            margin-right: 10px;
        }
        
        .employer-badge {
            display: inline-block;
            background-color: rgba(10, 102, 194, 0.1);
            color: var(--primary-color);
            font-size: 14px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .form-control {
            padding: 12px 15px;
            border-radius: 6px;
            border: 1px solid var(--light-gray);
            font-size: 16px;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 2px rgba(10, 102, 194, 0.2);
            border-color: var(--primary-color);
        }
        
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 15px;
            cursor: pointer;
            color: #777;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 12px;
            font-weight: 600;
            border-radius: 30px;
            width: 100%;
            margin-top: 10px;
            font-size: 16px;
        }
        
        .btn-primary:hover {
            background-color: #004182;
            border-color: #004182;
        }
        
        .or-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
            color: #666;
            font-size: 14px;
        }
        
        .or-divider::before, 
        .or-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .or-divider::before {
            margin-right: 15px;
        }
        
        .or-divider::after {
            margin-left: 15px;
        }
        
        .social-login {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .social-login-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            border: 1px solid var(--light-gray);
            border-radius: 30px;
            padding: 10px 15px;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            text-decoration: none;
            transition: all 0.2s;
            min-width: 140px;
        }
        
        .social-login-btn:hover {
            background-color: #f3f3f3;
        }
        
        .social-login-btn i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }
        
        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .register-prompt {
            text-align: center;
            margin-top: 25px;
            font-size: 15px;
        }
        
        .register-prompt a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }
        
        .register-prompt a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 8px;
            font-size: 14px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .alert i {
            margin-right: 8px;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 13px;
            color: #666;
        }
        
        .footer a {
            color: #666;
            text-decoration: none;
            margin: 0 10px;
        }
        
        .footer a:hover {
            text-decoration: underline;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="card">
            <div class="card-header text-center">
                <div class="logo">
                    <i class="fas fa-briefcase"></i> GoSeekr
                </div>
                <div class="employer-badge">
                    <i class="fas fa-building me-1"></i> Employer Portal
                </div>
            </div>
            <div class="card-body">
                <?php if ($message === 'invalid_credentials'): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> The email or password you entered is incorrect.
                    </div>
                <?php elseif ($message === 'account_not_verified'): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Your account is pending verification. Please contact our support team for assistance.
                    </div>
                <?php endif; ?>
                
                <h4 class="text-center mb-4">Sign in to your employer account</h4>
                
                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required autocomplete="email" placeholder="Enter your email">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-container">
                            <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
                            <span class="password-toggle" id="passwordToggle">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Sign In</button>
                </form>
                
                <!-- <div class="forgot-password">
                    <a href="forgot_password.php">Forgot password?</a>
                </div> -->
                
                <div class="register-prompt">
                    New to GoSeekr? <a href="register.php">Create an employer account</a>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <div class="mb-2">
                <a href="../">Job Seeker Login</a> | 
            </div>
            <div>
                &copy; 2023 GoSeekr. All rights reserved.
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordToggle = document.getElementById('passwordToggle');
            const passwordField = document.getElementById('password');
            
            passwordToggle.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                
                // Toggle eye icon
                const eyeIcon = passwordToggle.querySelector('i');
                eyeIcon.classList.toggle('fa-eye');
                eyeIcon.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>