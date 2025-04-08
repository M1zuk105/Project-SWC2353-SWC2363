<?php
require_once 'db_connect.php';
session_start();

$success = '';
$error = '';

// Get all products for the dropdown
$products = [];
$stmt = $conn->prepare("SELECT product_id, name FROM products");
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get product ID safely - check both POST and GET
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

// Get reviews for the selected product
$reviews = [];
$selected_product_name = '';
if ($product_id > 0) {
    // Get product details and reviews
    $stmt = $conn->prepare("
        SELECT r.*, u.name, p.name as product_name 
        FROM reviews r
        LEFT JOIN users u ON r.user_id = u.user_id
        JOIN products p ON r.product_id = p.product_id
        WHERE r.product_id = ?
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get product name
    $stmt = $conn->prepare("SELECT name FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $selected_product_name = $product['name'] ?? '';
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rating = intval($_POST['rating'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    $user_id = $_SESSION['user_id'] ?? null;

    // Validate inputs
    if ($product_id <= 0) {
        $error = "Please select a product.";
    } elseif (empty($name)) {
        $error = "Name is required.";
    } elseif ($rating < 1 || $rating > 5) {
        $error = "Please select a valid rating between 1 and 5.";
    } elseif (empty($message)) {
        $error = "Review message is required.";
    } else {
        // Insert the review
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating, comment, created_at) 
                                VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiis", $user_id, $product_id, $rating, $message);

        if ($stmt->execute()) {
            $success = "Thank you for your review!";
            $_POST = []; // Clear form
            
            // Refresh reviews after submission
            $stmt = $conn->prepare("
                SELECT r.*, u.name, p.name as product_name 
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.user_id
                JOIN products p ON r.product_id = p.product_id
                WHERE r.product_id = ?
            ");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $reviews = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            $error = "Error submitting review. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Reviews</title>

    <style>
        .review-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .review-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        .rating-stars {
            display: flex;
            gap: 10px;
            margin: 10px 0;
        }
        .rating-stars input[type="radio"] {
            display: none;
        }
        .rating-stars label {
            font-size: 24px;
            color: #ccc;
            cursor: pointer;
        }
        .rating-stars input[type="radio"]:checked ~ label {
            color: #ffcc00;
        }
        .rating-stars label:hover,
        .rating-stars label:hover ~ label {
            color: #ffcc00;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #45a049;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-success {
            background: #dff0d8;
            color: #3c763d;
        }
        .alert-error {
            background: #f2dede;
            color: #a94442;
        }
        .reviews-list {
            margin-top: 30px;
        }
        .review-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .review-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="review-container">
        <h1>Leave a Review</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form class="review-form" method="POST" action="">
    <div class="form-group">
        <label for="product">Select Product*</label>
        <select id="product" name="product_id" required>
            <option value="">-- Select a Product --</option>
            <?php foreach ($products as $product): ?>
                <option value="<?= htmlspecialchars($product['product_id']) ?>"
                    <?= $product['product_id'] == $product_id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($product['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="name">Your Name*</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label for="email">Your Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Your Rating*</label>
        <div class="rating-stars">
            <?php $current_rating = $_POST['rating'] ?? 0; ?>
            <input type="radio" id="star5" name="rating" value="5" <?= $current_rating == 5 ? 'checked' : '' ?> required>
            <label for="star5">★</label>
            <input type="radio" id="star4" name="rating" value="4" <?= $current_rating == 4 ? 'checked' : '' ?>>
            <label for="star4">★</label>
            <input type="radio" id="star3" name="rating" value="3" <?= $current_rating == 3 ? 'checked' : '' ?>>
            <label for="star3">★</label>
            <input type="radio" id="star2" name="rating" value="2" <?= $current_rating == 2 ? 'checked' : '' ?>>
            <label for="star2">★</label>
            <input type="radio" id="star1" name="rating" value="1" <?= $current_rating == 1 ? 'checked' : '' ?>>
            <label for="star1">★</label>
        </div>
    </div>

    <div class="form-group">
        <label for="message">Your Review*</label>
        <textarea id="message" name="message" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
    </div>

    <button type="submit">Submit Review</button>
</form>

        <?php if (!empty($reviews)): ?>
            <div class="reviews-list">
                <h2>Customer Reviews</h2>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-meta">
                            <span class="review-author"><?= htmlspecialchars($review['name'] ?? 'Anonymous') ?></span>
                            <span class="review-date"><?= date('F j, Y', strtotime($review['created_at'])) ?></span>
                        </div>
                        <div class="review-rating">
                            <?= str_repeat('★', $review['rating']) ?><?= str_repeat('☆', 5 - $review['rating']) ?>
                        </div>
                        <div class="review-content">
                            <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No reviews yet. Be the first to review!</p>
        <?php endif; ?>
    </div>
</body>
</html>