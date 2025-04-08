<?php
require_once 'header.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Add to cart functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = sanitizeInput($_POST['product_id']);
    $quantity = sanitizeInput($_POST['quantity']);
    
    // Check if product already in cart
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // Update quantity
        $sql = "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = $user_id AND product_id = $product_id";
    } else {
        // Add new item
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)";
    }
    
    $conn->query($sql);
}

// Remove from cart functionality
if (isset($_GET['remove'])) {
    $cart_id = sanitizeInput($_GET['remove']);
    $user_id = $_SESSION['user_id'];
    
    $sql = "DELETE FROM cart WHERE cart_id = $cart_id AND user_id = $user_id";
    $conn->query($sql);
}

// Update quantity functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $cart_id => $quantity) {
        $cart_id = sanitizeInput($cart_id);
        $quantity = sanitizeInput($quantity);
        $user_id = $_SESSION['user_id'];
        
        if ($quantity <= 0) {
            $sql = "DELETE FROM cart WHERE cart_id = $cart_id AND user_id = $user_id";
        } else {
            $sql = "UPDATE cart SET quantity = $quantity WHERE cart_id = $cart_id AND user_id = $user_id";
        }
        
        $conn->query($sql);
    }
}

// Get cart items
$user_id = $_SESSION['user_id'];
$sql = "SELECT c.cart_id, c.quantity, p.product_id, p.name, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.user_id = $user_id";
$result = $conn->query($sql);
$cart_items = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total += $row['price'] * $row['quantity'];
}
?>

<section class="cart">
    <h2>Your Shopping Cart</h2>
    
    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty. <a href="products.php">Continue shopping</a></p>
    <?php else: ?>
        <form method="post" action="cart.php">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td>
                                <img src="images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" width="50">
                                <?php echo $item['name']; ?>
                            </td>
                            <td>RM<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <input type="number" name="quantities[<?php echo $item['cart_id']; ?>]" 
                                       value="<?php echo $item['quantity']; ?>" min="1">
                            </td>
                            <td>RM<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td>
                                <a href="cart.php?remove=<?php echo $item['cart_id']; ?>" class="btn btn-danger">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">Total</td>
                        <td colspan="2">RM<?php echo number_format($total, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
            <div class="cart-actions">
                <button type="submit" name="update_cart" class="btn">Update Cart</button>
                <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
            </div>
        </form>
    <?php endif; ?>
</section>

<?php
require_once 'footer.php';
?>