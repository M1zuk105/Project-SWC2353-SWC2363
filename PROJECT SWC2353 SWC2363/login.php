<?php
require_once 'db_connect.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $login_type = $_POST['login_type']; // 'admin' or 'customer'

    // Input validation
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        // Fetch user without filtering by role
        $stmt = $conn->prepare("SELECT user_id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Check password
            if (password_verify($password, $user['password'])) {
                // Check if the login type matches the stored role
                if ($user['role'] === $login_type) {
                    // Login successful - set session
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['email'] = $email;
                    $_SESSION['logged_in'] = true;

                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header("Location: dashboard.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                } else {
                    $error = "Incorrect login type selected.";
                }
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No account found with that email.";
        }
    }
}

// Admin password reset feature (for testing, remove in production)
if (isset($_GET['reset_admin']) && $_GET['reset_admin'] === '1') {
    $new_hash = password_hash('admin321', PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password = '$new_hash' WHERE email = 'admin@gmail.com'");
    $error = "Admin password has been reset to 'admin321' (remove this in production)";
}

?>


<!DOCTYPE html>
<html>
<head>
    <title>Login | Zeus Tech</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 350px; }
        .login-container h1 { text-align: center; margin-bottom: 20px; color: #333; }
        .alert.error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; }
        .login-type { display: flex; margin-bottom: 15px; }
        .login-type label { flex: 1; text-align: center; padding: 10px; cursor: pointer; border: 1px solid #ddd; }
        .login-type label:first-child { border-radius: 4px 0 0 4px; border-right: none; }
        .login-type label:last-child { border-radius: 0 4px 4px 0; }
        .login-type input[type="radio"] { display: none; }
        .login-type input[type="radio"]:checked + label { background: #007bff; color: white; border-color: #007bff; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0069d9; }
        .register-link { text-align: center; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <?php if (!empty($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="login-type">
                <input type="radio" id="customer-login" name="login_type" value="customer" checked>
                <label for="customer-login">Customer</label>
                
                <input type="radio" id="admin-login" name="login_type" value="admin">
                <label for="admin-login">Admin</label>
            </div>
            
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            
            <div class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </form>
    </div>
</body>
</html>