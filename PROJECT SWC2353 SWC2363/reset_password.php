<?php
session_start();
require_once 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $email = $_POST['email'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        
        // Correct column name and verify if role column exists
        $sql = "UPDATE users SET password = ? WHERE email = ? AND role = 'admin'";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ss", $hashed_password, $email);
            
            if ($stmt->execute()) {
                $success = "Password reset successfully. You can now <a href='login.php'>login</a>.";
            } else {
                $error = "Failed to reset password. Please try again.";
            }
            
            $stmt->close();
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Zeus Tech Gadget Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f5f7fa; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); text-align: center; }
        .error { color: red; margin-bottom: 10px; }
        .success { color: green; margin-bottom: 10px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { background: #d72631; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #b81e2a; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Admin Password</h2>
        <?php if (!empty($error)): ?>
            <p class="error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p class="success"><i class="fas fa-check-circle"></i> <?= $success ?></p>
        <?php endif; ?>
        <form method="post" action="reset_password.php">
            <input type="email" name="email" placeholder="Admin Email" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>