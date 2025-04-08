<?php
// Start PHP session
session_start();

// Include database connection file
require_once 'db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        // Add new user
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $role = $_POST['role'];
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, role) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $password, $phone, $address, $role);
        $stmt->execute();
        
        $_SESSION['success'] = "User added successfully!";
        header("Location: userlist.php");
        exit();
    } elseif (isset($_POST['update_user'])) {
        // Update existing user
        $user_id = intval($_POST['user_id']);
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $role = $_POST['role'];
        
        // Check if password was provided
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET 
                                  name = ?, email = ?, password = ?, phone = ?, 
                                  address = ?, role = ?
                                  WHERE user_id = ?");
            $stmt->bind_param("ssssssi", $name, $email, $password, $phone, $address, $role, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET 
                                  name = ?, email = ?, phone = ?, 
                                  address = ?, role = ?
                                  WHERE user_id = ?");
            $stmt->bind_param("sssssi", $name, $email, $phone, $address, $role, $user_id);
        }
        
        $stmt->execute();
        
        $_SESSION['success'] = "User updated successfully!";
        header("Location: userlist.php");
        exit();
    } elseif (isset($_POST['delete_user'])) {
        // Delete user (don't allow self-deletion)
        $user_id = intval($_POST['user_id']);
        
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot delete your own account!";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            $_SESSION['success'] = "User deleted successfully!";
        }
        
        header("Location: userlist.php");
        exit();
    }
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role = isset($_GET['role']) ? trim($_GET['role']) : '';

// Build the base query
$query = "SELECT * FROM users";
$where = [];
$params = [];
$types = '';

// Add search filter
if (!empty($search)) {
    $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

// Add role filter
if (!empty($role)) {
    $where[] = "role = ?";
    $params[] = $role;
    $types .= 's';
}

// Combine WHERE conditions
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

// Complete the query
$query .= " ORDER BY created_at DESC";

// Prepare and execute the query
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$users = $stmt->get_result();

// Get user roles for filter dropdown
$roles = ['admin', 'customer']; // Changed from ['admin', 'user', 'moderator']
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | Zeus Tech</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/admindashboard.css">
    <style>
        /* Updated role badge styles */
        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
        }
        .role-admin {
            background-color: #ff9800;
            color: white;
        }
        .role-customer {
            background-color: #4caf50;
            color: white;
        }
    </style>
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
                <a href="orderlist.php" class="menu-item">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="userlist.php" class="menu-item active">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-users"></i> User Management</h1>
                <button class="btn-primary" onclick="document.getElementById('addUserModal').style.display='block'">
                    <i class="fas fa-plus"></i> Add User
                </button>
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

            <!-- User Filter Form -->
            <div class="filter-section">
                <form method="get" class="filter-form">
                    <div class="search-group">
                        <input type="text" name="search" placeholder="Search users..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <span class="enter-hint">Press Enter to filter</span>
                    </div>
                    <select name="role">
                        <option value="">All Roles</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?php echo $r; ?>"
                                <?php if ($role == $r) echo 'selected'; ?>>
                                <?php echo ucfirst($r); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="userlist.php" class="btn-secondary">
                        <i class="fas fa-sync-alt"></i> Reset
                    </a>
                </form>
            </div>

            <!-- Users Table -->
            <div class="section">
                <?php if ($users->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo $user['role']; ?>">
                                            <?php 
                                            // Display 'Customer' instead of 'user'
                                            echo $user['role'] === 'user' ? 'Customer' : ucfirst($user['role']); 
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <button class="action-btn btn-primary" 
                                                onclick="openEditModal(<?php echo $user['user_id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <button type="submit" name="delete_user" class="action-btn btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this user?')"
                                                    <?php if ($user['user_id'] == $_SESSION['user_id']) echo 'disabled'; ?>>
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-user-slash"></i>
                        <p>No users found matching your criteria</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addUserModal').style.display='none'">&times;</span>
            <h2><i class="fas fa-plus"></i> Add New User</h2>
            <form method="post">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="customer">Customer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" name="add_user" class="btn-primary">
                    <i class="fas fa-save"></i> Save User
                </button>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editUserModal').style.display='none'">&times;</span>
            <h2><i class="fas fa-edit"></i> Edit User</h2>
            <form method="post" id="editForm">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="form-group">
                    <label for="edit_name">Full Name</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="edit_password">New Password (leave blank to keep current)</label>
                    <input type="password" id="edit_password" name="password">
                </div>
                <div class="form-group">
                    <label for="edit_phone">Phone Number</label>
                    <input type="text" id="edit_phone" name="phone">
                </div>
                <div class="form-group">
                    <label for="edit_address">Address</label>
                    <textarea id="edit_address" name="address" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_role">Role</label>
                    <select id="edit_role" name="role" required>
                        <option value="customer">Customer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" name="update_user" class="btn-primary">
                    <i class="fas fa-save"></i> Update User
                </button>
            </form>
        </div>
    </div>

    <script>
        // Function to open edit modal with user data
        function openEditModal(userId) {
            // Fetch user data via AJAX
            fetch('get_user.php?id=' + userId)
                .then(response => response.json())
                .then(user => {
                    // Populate form fields
                    document.getElementById('edit_user_id').value = user.user_id;
                    document.getElementById('edit_name').value = user.name;
                    document.getElementById('edit_email').value = user.email;
                    document.getElementById('edit_phone').value = user.phone;
                    document.getElementById('edit_address').value = user.address;
                    
                    // Set role - convert 'user' to 'customer' if needed
                    const role = user.role === 'user' ? 'customer' : user.role;
                    document.getElementById('edit_role').value = role;
                    
                    // Show modal
                    document.getElementById('editUserModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load user data');
                });
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
        
        // Submit form on Enter key in filter inputs
        document.addEventListener('DOMContentLoaded', function() {
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