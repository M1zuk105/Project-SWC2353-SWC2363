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

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product'])) {
        // Add new product
        $name = $_POST['name'];
        $brand = $_POST['brand'];
        $description = $_POST['description'];
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);
        $category = $_POST['category'];
        $rating = floatval($_POST['rating']);
        
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/products/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_ext;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $target_file;
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO products (name, brand, description, price, stock, category, image, rating) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdisss", $name, $brand, $description, $price, $stock, $category, $image, $rating);
        $stmt->execute();
        
        $_SESSION['success'] = "Product added successfully!";
        header("Location: productlist.php");
        exit();
    } elseif (isset($_POST['update_product'])) {
        // Update existing product
        $product_id = intval($_POST['product_id']);
        $name = $_POST['name'];
        $brand = $_POST['brand'];
        $description = $_POST['description'];
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);
        $category = $_POST['category'];
        $rating = floatval($_POST['rating']);
        
        // Handle image update
        $image = $_POST['current_image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/products/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_ext;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // Delete old image if exists
                if (!empty($image) && file_exists($image)) {
                    unlink($image);
                }
                $image = $target_file;
            }
        }
        
        $stmt = $conn->prepare("UPDATE products SET 
                              name = ?, brand = ?, description = ?, price = ?, 
                              stock = ?, category = ?, image = ?, rating = ?
                              WHERE product_id = ?");
        $stmt->bind_param("sssdisssi", $name, $brand, $description, $price, $stock, $category, $image, $rating, $product_id);
        $stmt->execute();
        
        $_SESSION['success'] = "Product updated successfully!";
        header("Location: productlist.php");
        exit();
    } elseif (isset($_POST['delete_product'])) {
        // Delete product
        $product_id = intval($_POST['product_id']);
        
        // First get image path to delete it
        $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        if ($product && !empty($product['image']) && file_exists($product['image'])) {
            unlink($product['image']);
        }
        
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        $_SESSION['success'] = "Product deleted successfully!";
        header("Location: productlist.php");
        exit();
    }
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Build the base query
$query = "SELECT * FROM products";
$where = [];
$params = [];
$types = '';

// Add search filter
if (!empty($search)) {
    $where[] = "(name LIKE ? OR brand LIKE ? OR description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

// Add category filter
if (!empty($category)) {
    $where[] = "category = ?";
    $params[] = $category;
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
$products = $stmt->get_result();

// Get distinct categories for dropdown
$categories_query = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL";
$categories = $conn->query($categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management | Zeus Tech</title>
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
                <a href="productlist.php" class="menu-item active">
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

        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-boxes"></i> Product Management</h1>
                <button class="btn-primary" onclick="document.getElementById('addProductModal').style.display='block'">
                    <i class="fas fa-plus"></i> Add Product
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

            <!-- Product Filter Form -->
            <div class="filter-section">
                <form method="get" class="filter-form">
                    <div class="search-group">
                        <input type="text" name="search" placeholder="Search products..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <span class="enter-hint">Press Enter to filter</span>
                    </div>
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>"
                                <?php if ($category == $cat['category']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="productlist.php" class="btn-secondary">
                        <i class="fas fa-sync-alt"></i> Reset
                    </a>
                </form>
            </div>

            <!-- Products Table -->
            <div class="section">
                <?php if ($products->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Category</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $products->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $product['product_id']; ?></td>
                                    <td>
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="images/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="no-image">No Image</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['brand']); ?></td>
                                    <td>RM <?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td><?php echo htmlspecialchars($product['category']); ?></td>
                                    <td>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?php if ($i > $product['rating']) echo '-half-alt'; ?>"></i>
                                            <?php endfor; ?>
                                            <span>(<?php echo $product['rating']; ?>)</span>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="action-btn btn-primary" 
                                                onclick="openEditModal(<?php echo $product['product_id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                            <button type="submit" name="delete_product" class="action-btn btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this product?')">
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
                        <i class="fas fa-box-open"></i>
                        <p>No products found matching your criteria</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addProductModal').style.display='none'">&times;</span>
            <h2><i class="fas fa-plus"></i> Add New Product</h2>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="brand">Brand</label>
                    <input type="text" id="brand" name="brand">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (RM)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" id="stock" name="stock" min="0" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category">
                    </div>
                    <div class="form-group">
                        <label for="rating">Rating (0-5)</label>
                        <input type="number" id="rating" name="rating" step="0.1" min="0" max="5" value="0">
                    </div>
                </div>
                <div class="form-group">
                    <label for="image">Product Image</label>
                    <input type="file" id="image" name="image" accept="images/*">
                </div>
                <button type="submit" name="add_product" class="btn-primary">
                    <i class="fas fa-save"></i> Save Product
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editProductModal').style.display='none'">&times;</span>
            <h2><i class="fas fa-edit"></i> Edit Product</h2>
            <form method="post" enctype="multipart/form-data" id="editForm">
                <input type="hidden" name="product_id" id="edit_product_id">
                <input type="hidden" name="current_image" id="current_image">
                <div class="form-group">
                    <label for="edit_name">Product Name</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit_brand">Brand</label>
                    <input type="text" id="edit_brand" name="brand">
                </div>
                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_price">Price (RM)</label>
                        <input type="number" id="edit_price" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_stock">Stock</label>
                        <input type="number" id="edit_stock" name="stock" min="0" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_category">Category</label>
                        <input type="text" id="edit_category" name="category">
                    </div>
                    <div class="form-group">
                        <label for="edit_rating">Rating (0-5)</label>
                        <input type="number" id="edit_rating" name="rating" step="0.1" min="0" max="5">
                    </div>
                </div>
                <div class="form-group">
                    <label>Current Image</label>
                    <div id="current_image_container" style="margin-bottom: 10px;"></div>
                    <label for="edit_image">Change Image</label>
                    <input type="file" id="edit_image" name="image" accept="image/*">
                </div>
                <button type="submit" name="update_product" class="btn-primary">
                    <i class="fas fa-save"></i> Update Product
                </button>
            </form>
        </div>
    </div>

    <script>
        // Function to open edit modal with product data
        function openEditModal(productId) {
            // Fetch product data via AJAX
            fetch('get_product.php?id=' + productId)
                .then(response => response.json())
                .then(product => {
                    // Populate form fields
                    document.getElementById('edit_product_id').value = product.product_id;
                    document.getElementById('edit_name').value = product.name;
                    document.getElementById('edit_brand').value = product.brand;
                    document.getElementById('edit_description').value = product.description;
                    document.getElementById('edit_price').value = product.price;
                    document.getElementById('edit_stock').value = product.stock;
                    document.getElementById('edit_category').value = product.category;
                    document.getElementById('edit_rating').value = product.rating;
                    document.getElementById('current_image').value = product.image;
                    
                    // Display current image
                    const imageContainer = document.getElementById('current_image_container');
                    imageContainer.innerHTML = '';
                    if (product.image) {
                        const img = document.createElement('img');
                        img.src = product.image;
                        img.style.width = '100px';
                        img.style.height = '100px';
                        img.style.objectFit = 'cover';
                        imageContainer.appendChild(img);
                    } else {
                        imageContainer.innerHTML = '<div>No image</div>';
                    }
                    
                    // Show modal
                    document.getElementById('editProductModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load product data');
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