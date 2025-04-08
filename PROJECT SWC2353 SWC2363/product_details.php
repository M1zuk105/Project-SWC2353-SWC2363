<?php
require_once 'header.php';

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$productId = $_GET['id'];
$product = getProductById($productId);

if (!$product) {
    header("Location: products.php");
    exit();
}
?>

<section class="product-details">
    <div class="product-image">
        <img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
    </div>
    <div class="product-info">
        <h2><?php echo $product['name']; ?></h2>
        <p class="brand">Brand: <?php echo $product['brand']; ?></p>
        <p class="price">RM<?php echo number_format($product['price'], 2); ?></p>
        <p class="stock"><?php echo $product['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?></p>
        <p class="rating">Rating: <?php echo $product['rating']; ?>/5</p>
        <p class="description"><?php echo $product['description']; ?></p>
        
        <?php if ($product['stock'] > 0 && isLoggedIn()): ?>
            <form action="cart.php" method="post">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1">
                <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
            </form>
        <?php elseif (!isLoggedIn()): ?>
            <p>Please <a href="login.php">login</a> to add items to cart.</p>
        <?php endif; ?>
    </div>
</section>

<section class="reviews">
    <h3>Customer Reviews</h3>
    <?php
    // Get reviews for this product
    global $conn;
    $sql = "SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.user_id WHERE product_id = $productId ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($review = $result->fetch_assoc()) {
            echo '<div class="review">';
            echo '<h4>' . htmlspecialchars($review['user_name']) . '</h4>';
            echo '<p>Rating: ' . $review['rating'] . '/5</p>';
            echo '<p>' . htmlspecialchars($review['comment']) . '</p>';
            echo '<p class="date">' . date('M d, Y', strtotime($review['created_at'])) . '</p>';
            echo '</div>';
        }
    } else {
        echo '<p>No reviews yet. Be the first to review!</p>';
    }
    
    // Add review form if user is logged in
    if (isLoggedIn()) {
        echo '<form method="post" action="functions.php?action=add_review">';
        echo '<input type="hidden" name="product_id" value="' . $productId . '">';
        echo '<h4>Add Your Review</h4>';
        echo '<label for="rating">Rating:</label>';
        echo '<select name="rating" id="rating" required>';
        echo '<option value="1">1 - Poor</option>';
        echo '<option value="2">2 - Fair</option>';
        echo '<option value="3">3 - Good</option>';
        echo '<option value="4">4 - Very Good</option>';
        echo '<option value="5">5 - Excellent</option>';
        echo '</select>';
        echo '<label for="comment">Comment:</label>';
        echo '<textarea name="comment" id="comment" required></textarea>';
        echo '<button type="submit" class="btn">Submit Review</button>';
        echo '</form>';
    }
    ?>
</section>

<?php
require_once 'footer.php';
?>