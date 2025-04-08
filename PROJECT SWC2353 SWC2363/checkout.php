<?php
require_once 'header.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Get user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Get cart items
$sql = "SELECT c.quantity, p.product_id, p.name, p.price 
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

// Process checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($cart_items)) {
    // Create order
    $sql = "INSERT INTO orders (user_id, total_price) VALUES ($user_id, $total)";
    $conn->query($sql);
    $order_id = $conn->insert_id;
    
    // Add order items
    foreach ($cart_items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                VALUES ($order_id, $product_id, $quantity, $price)";
        $conn->query($sql);
        
        // Update product stock
        $sql = "UPDATE products SET stock = stock - $quantity WHERE product_id = $product_id";
        $conn->query($sql);
    }
    
    // Clear cart
    $sql = "DELETE FROM cart WHERE user_id = $user_id";
    $conn->query($sql);
    
    // Redirect to order confirmation
    header("Location: order_confirmation.php?order_id=$order_id");
    exit();
}
?>

<section class="checkout">
    <h2>Checkout</h2>
    
    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty. <a href="products.php">Continue shopping</a></p>
    <?php else: ?>
        <div class="checkout-grid">
            <div class="shipping-info">
                <h3>Shipping Information</h3>
                <form method="post">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                    
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                    
                    <label for="phone">Phone Number:</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required>
                    
                    <label for="address">Shipping Address:</label>
                    <textarea id="address" name="address" required><?php echo $user['address']; ?></textarea>
                    
                    <h3>Payment Method</h3>
                    <div class="payment-method">
                        <input type="radio" id="credit_card" name="payment_method" value="credit_card" checked>
                        <label for="credit_card">Credit Card</label><br>
                        
                        <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer">
                        <label for="bank_transfer">Bank Transfer</label><br>
                        
                        <input type="radio" id="cod" name="payment_method" value="cod">
                        <label for="cod">Cash on Delivery</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Place Order</button>
                </form>
            </div>
            
            <div class="order-summary">
                <h3>Order Summary</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><?php echo $item['name']; ?></td>
                                <td>RM<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>RM<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">Subtotal</td>
                            <td>RM<?php echo number_format($total, 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3">Shipping</td>
                            <td>RM5.00</td>
                        </tr>
                        <tr>
                            <td colspan="3">Total</td>
                            <td>RM<?php echo number_format($total + 5, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php
require_once 'footer.php';
?>