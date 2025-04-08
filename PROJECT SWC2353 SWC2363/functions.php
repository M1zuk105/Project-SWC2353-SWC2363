<?php
require_once 'db_connect.php';

// Function to sanitize input data
function sanitizeInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] == 'admin');
}

// Function to get product by ID
function getProductById($id) {
    global $conn;
    $id = sanitizeInput($id);
    $sql = "SELECT * FROM products WHERE product_id = $id";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}
// Function to get all products with optional category, search, and price filters
function getAllProducts($category = null, $searchQuery = '', $minPrice = null, $maxPrice = null) {
    global $conn;
    
    $sql = "SELECT * FROM products WHERE 1=1";
    $types = '';
    $params = [];
    
    // Add category filter if provided
    if ($category) {
        $category = sanitizeInput($category);
        $sql .= " AND category = ?";
        $types .= 's'; // 's' for string type
        $params[] = &$category;
    }
    
    // Add search filter if provided
    if (!empty($searchQuery)) {
        $searchQuery = sanitizeInput($searchQuery);
        $searchTerm = "%$searchQuery%";
        $sql .= " AND (name LIKE ? OR description LIKE ?)";
        $types .= 'ss'; // Two string parameters
        $params[] = &$searchTerm;
        $params[] = &$searchTerm;
    }
    
    // Add minimum price filter if provided
    if ($minPrice !== null && is_numeric($minPrice)) {
        $minPrice = (float)$minPrice;
        $sql .= " AND price >= ?";
        $types .= 'd'; // 'd' for double type
        $params[] = &$minPrice;
    }
    
    // Add maximum price filter if provided
    if ($maxPrice !== null && is_numeric($maxPrice)) {
        $maxPrice = (float)$maxPrice;
        $sql .= " AND price <= ?";
        $types .= 'd'; // 'd' for double type
        $params[] = &$maxPrice;
    }
    
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters if any
    if (!empty($params)) {
        // Prepend the types string to the params array
        array_unshift($params, $types);
        
        // Use call_user_func_array to bind parameters dynamically
        call_user_func_array(array($stmt, 'bind_param'), $params);
    }
    
    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch results
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    // Close statement
    $stmt->close();
    
    return $products;
}
?>