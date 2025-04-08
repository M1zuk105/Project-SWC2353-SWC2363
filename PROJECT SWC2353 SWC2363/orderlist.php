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

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['new_status'];
    
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        
        // Log this action in admin logs
        $admin_id = $_SESSION['user_id'];
        $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, 'order_status', ?)");
        $log_details = "Changed order #$order_id to $new_status";
        $log_stmt->bind_param("is", $admin_id, $log_details);
        $log_stmt->execute();
        
        $_SESSION['success'] = "Order status updated successfully!";
    } else {
        $_SESSION['error'] = "Invalid order status";
    }
    
    header("Location: orderlist.php" . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
    exit();
}

// Get filter parameters from GET request
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build the base query
$query = "
    SELECT o.order_id, u.name as customer_name, u.email, o.status, 
           o.total_price, o.created_at, COUNT(od.order_details_id) as item_count
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    LEFT JOIN order_details od ON o.order_id = od.order_id
";

// Add WHERE conditions based on filters
$where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(u.name LIKE ? OR u.email LIKE ? OR o.order_id LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'sss';
}

if (!empty($status)) {
    $where[] = "o.status = ?";
    $params[] = $status;
    $types .= 's';
}

if (!empty($date_from)) {
    $where[] = "DATE(o.created_at) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $where[] = "DATE(o.created_at) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

// Combine WHERE conditions
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

// Complete the query
$query .= " GROUP BY o.order_id ORDER BY o.created_at DESC";

// Prepare and execute the query
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$orders = $stmt->get_result();

// Get order statuses for filter dropdown
$statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management | Zeus Tech</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/admindashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar navigation -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Zeus Tech</h2>
                <p>Admin Panel</p>
            </div>
            
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="productlist.php" class="menu-item">
                    <i class="fas fa-boxes"></i> Products
                </a>
                <a href="orderlist.php" class="menu-item active">
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

        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-shopping-cart"></i> Order Management</h1>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']); 
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']); 
                    ?>
                </div>
            <?php endif; ?>

            <!-- Order Filter Form -->
            <div class="filter-section">
                <form method="get" class="filter-form">
                    <div class="search-group">
                        <input type="text" name="search" placeholder="Search orders..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <span class="enter-hint">Press Enter to filter</span>
                    </div>
                    <select name="status">
                        <option value="">All Statuses</option>
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?php echo $s; ?>"
                                <?php if ($status == $s) echo 'selected'; ?>>
                                <?php echo ucfirst($s); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" name="date_from" 
                           value="<?php echo htmlspecialchars($date_from); ?>">
                    <input type="date" name="date_to" 
                           value="<?php echo htmlspecialchars($date_to); ?>">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="orderlist.php" class="btn-secondary">
                        <i class="fas fa-sync-alt"></i> Reset
                    </a>
                </form>
            </div>

            <!-- Orders Table -->
            <div class="section">
                <?php if ($orders->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $order['item_count']; ?></td>
                                    <td>RM <?php echo number_format($order['total_price'], 2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <!-- Status update form -->
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                            <select name="new_status" onchange="this.form.submit()" class="form-control" 
                                                    style="width: auto; display: inline-block;">
                                                <option value="">Change Status</option>
                                                <option value="pending" <?php if ($order['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                                <option value="processing" <?php if ($order['status'] == 'processing') echo 'selected'; ?>>Processing</option>
                                                <option value="shipped" <?php if ($order['status'] == 'shipped') echo 'selected'; ?>>Shipped</option>
                                                <option value="delivered" <?php if ($order['status'] == 'delivered') echo 'selected'; ?>>Delivered</option>
                                                <option value="cancelled" <?php if ($order['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-box-open"></i>
                        <p>No orders found matching your criteria</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Add confirmation for cancelling orders
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelects = document.querySelectorAll('select[name="new_status"]');
            
            statusSelects.forEach(function(select) {
                select.addEventListener('change', function() {
                    if (this.value === 'cancelled') {
                        if (!confirm('Are you sure you want to cancel this order?')) {
                            this.value = '';
                            return false;
                        }
                    }
                    return true;
                });
            });
            
            // Submit form on Enter key in any input
            const filterForm = document.querySelector('.filter-form');
            if (filterForm) {
                filterForm.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.submit();
                    }
                });
            }
        });
    </script>
</body>
</html>