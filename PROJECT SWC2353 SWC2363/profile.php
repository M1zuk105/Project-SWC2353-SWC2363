<?php
require_once 'header.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = sanitizeInput($_POST['name']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    
    $sql = "UPDATE users SET name = '$name', phone = '$phone', address = '$address' WHERE user_id = $user_id";
    
    if ($conn->query($sql) === TRUE) {
        $_SESSION['name'] = $name;
        $success = "Profile updated successfully!";
    } else {
        $error = "Error updating profile: " . $conn->error;
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = sanitizeInput($_POST['current_password']);
    $new_password = sanitizeInput($_POST['new_password']);
    $confirm_password = sanitizeInput($_POST['confirm_password']);
    
    // Verify current password
    $sql = "SELECT password FROM users WHERE user_id = $user_id";
    $result = $conn->query($sql);
    $user = $result->fetch_assoc();
    
    if (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect!";
    } elseif ($new_password != $confirm_password) {
        $error = "New passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = '$hashed_password' WHERE user_id = $user_id";
        
        if ($conn->query($sql)) {
            $success = "Password changed successfully!";
        } else {
            $error = "Error changing password: " . $conn->error;
        }
    }
}

// Get user details
$sql = "SELECT * FROM users WHERE user_id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>

<section class="profile">
    <h2>My Profile</h2>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="profile-grid">
        <div class="profile-info">
            <h3>Personal Information</h3>
            <form method="post">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" disabled>
                
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                
                <label for="address">Address:</label>
                <textarea id="address" name="address"><?php echo $user['address']; ?></textarea>
                
                <button type="submit" name="update_profile" class="btn">Update Profile</button>
            </form>
        </div>
        
        <div class="change-password">
            <h3>Change Password</h3>
            <form method="post">
                <label for="current_password">Current Password:</label>
                <input type="password" id="current_password" name="current_password" required>
                
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
                
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                
                <button type="submit" name="change_password" class="btn">Change Password</button>
            </form>
        </div>
    </div>
    
    <div class="order-history">
        <h3>Order History</h3>
        <?php
        $sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while ($order = $result->fetch_assoc()) {
                echo '<div class="order">';
                echo '<h4>Order #' . $order['order_id'] . ' - ' . date('M d, Y', strtotime($order['created_at'])) . '</h4>';
                echo '<p>Status: ' . ucfirst($order['status']) . '</p>';
                echo '<p>Total: RM' . number_format($order['total_price'], 2) . '</p>';
                echo '<a href="order_details.php?order_id=' . $order['order_id'] . '" class="btn">View Details</a>';
                echo '</div>';
            }
        } else {
            echo '<p>No orders yet. <a href="products.php">Start shopping</a></p>';
        }
        ?>
    </div>
</section>

<?php
require_once 'footer.php';
?>