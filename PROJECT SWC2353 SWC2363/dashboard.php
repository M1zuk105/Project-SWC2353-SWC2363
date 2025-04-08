<?php
// Start PHP session
session_start();

// Include database connection file
require_once 'db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not admin
    header("Location: login.php");
    exit();
}

// Handle order status updates when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    
    // Get order ID and new status from form
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['new_status'];
    
    // List of allowed status values
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    // Check if submitted status is valid
    if (in_array($new_status, $allowed_statuses)) {
        
        // Update order status in database
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        
        // Log this action in admin logs
        $admin_id = $_SESSION['user_id'];
        $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, 'order_status', ?)");
        $log_details = "Changed order #$order_id to $new_status";
        $log_stmt->bind_param("is", $admin_id, $log_details);
        $log_stmt->execute();
        
        // Set success message
        $_SESSION['success'] = "Order status updated successfully!";
    } else {
        // Set error message if status is invalid
        $_SESSION['error'] = "Invalid order status";
    }
    
    // Redirect back to dashboard
    header("Location: dashboard.php");
    exit();
}

// Get statistics for dashboard
// 1. Count total products
$product_count_result = $conn->query("SELECT COUNT(*) FROM products");
$product_count = $product_count_result->fetch_row()[0];

// 2. Count pending orders
$order_count_result = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$order_count = $order_count_result->fetch_row()[0];

// 3. Count total users
$user_count_result = $conn->query("SELECT COUNT(*) FROM users");
$user_count = $user_count_result->fetch_row()[0];

// Get recent orders (last 10 orders)
$recent_orders_query = "
    SELECT o.order_id, u.name as customer_name, o.status, o.total_price, o.created_at
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.created_at DESC
    LIMIT 10
";
$recent_orders = $conn->query($recent_orders_query);

// Get orders pending approval
$pending_orders_query = "
    SELECT o.order_id, u.name as customer_name, o.total_price, o.created_at
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.status = 'pending'
    ORDER BY o.created_at
";
$pending_orders = $conn->query($pending_orders_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Zeus Tech</title>
    
    <!-- Include Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Link to CSS file -->
    <link rel="stylesheet" href="styles/admindashboard.css">
</head>
<body>
    <!-- Main dashboard container -->
    <div class="dashboard-container">
        
        <!-- Sidebar navigation -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Zeus Tech</h2>
                <p>Admin Panel</p>
            </div>
            
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="productlist.php" class="menu-item">
                    <i class="fas fa-boxes"></i> Products
                </a>
                <a href="orderlist.php" class="menu-item">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="userlist.php" class="menu-item">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main content area -->
        <div class="main-content">
            <!-- Page header -->
            <div class="header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
            </div>

            <!-- Display success messages if any -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']); 
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- Display error messages if any -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']); 
                    ?>
                </div>
            <?php endif; ?>

            <!-- Statistics cards -->
            <div class="stats-container">
                <!-- Products card -->
                <div class="stat-card">
                    <h3><i class="fas fa-box"></i> Total Products</h3>
                    <p><?php echo number_format($product_count); ?></p>
                </div>
                
                <!-- Orders card -->
                <div class="stat-card">
                    <h3><i class="fas fa-shopping-cart"></i> Pending Orders</h3>
                    <p><?php echo number_format($order_count); ?></p>
                </div>
                
                <!-- Users card -->
                <div class="stat-card">
                    <h3><i class="fas fa-users"></i> Registered Users</h3>
                    <p><?php echo number_format($user_count); ?></p>
                </div>
            </div>

            <!-- Pending approval section -->
            <div class="section">
                <h2 class="section-title"><i class="fas fa-clock"></i> Pending Approval</h2>
                
                <?php if ($pending_orders->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $pending_orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td>RM <?php echo number_format($order['total_price'], 2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <!-- Status update form -->
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                            <select name="new_status" onchange="this.form.submit()" class="form-control" style="width: auto; display: inline-block;">
                                                <option value="">Change Status</option>
                                                <option value="processing">Processing</option>
                                                <option value="shipped">Shipped</option>
                                                <option value="cancelled" style="color: var(--danger)">Cancel</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                        <!-- View order button -->
                                        <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="action-btn btn-primary" style="text-decoration: none;">
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No pending orders requiring approval.</p>
                <?php endif; ?>
            </div>

            <!-- Recent orders section -->
            <div class="section">
                <h2 class="section-title"><i class="fas fa-history"></i> Recent Orders</h2>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>RM <?php echo number_format($order['total_price'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <!-- Status update form -->
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <select name="new_status" onchange="this.form.submit()" class="form-control" style="width: auto; display: inline-block;">
                                            <option value="">Change Status</option>
                                            <option value="processing" <?php if ($order['status'] == 'processing') echo 'selected'; ?>>Processing</option>
                                            <option value="shipped" <?php if ($order['status'] == 'shipped') echo 'selected'; ?>>Shipped</option>
                                            <option value="delivered" <?php if ($order['status'] == 'delivered') echo 'selected'; ?>>Delivered</option>
                                            <option value="cancelled" <?php if ($order['status'] == 'cancelled') echo 'selected'; ?>>Cancel</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JavaScript for confirmation dialog -->
    <script>
        // Run this when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Get all status dropdowns
            const statusSelects = document.querySelectorAll('select[name="new_status"]');
            
            // Add event listener to each dropdown
            statusSelects.forEach(function(select) {
                select.addEventListener('change', function() {
                    // Check if user selected "cancelled"
                    if (this.value === 'cancelled') {
                        // Show confirmation dialog
                        if (!confirm('Are you sure you want to cancel this order?')) {
                            // If user cancels, reset the dropdown
                            this.value = '';
                            return false;
                        }
                    }
                    return true;
                });
            });
        });
    </script>
</body>
</html>