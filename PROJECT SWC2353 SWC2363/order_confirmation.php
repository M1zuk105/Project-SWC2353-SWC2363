<?php
require_once 'header.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header("Location: products.php");
    exit();
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Verify the order belongs to the current user
$sql = "SELECT o.*, u.name, u.email, u.phone, u.address 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        WHERE o.order_id = $order_id AND o.user_id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    // Order doesn't exist or doesn't belong to user
    header("Location: products.php");
    exit();
}

$order = $result->fetch_assoc();

// Get order items
$sql = "SELECT oi.*, p.name, p.image 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.product_id 
        WHERE oi.order_id = $order_id";
$result = $conn->query($sql);
$order_items = [];

while ($row = $result->fetch_assoc()) {
    $order_items[] = $row;
}
?>

<section class="order-confirmation">
    <div class="confirmation-header">
        <h2>Order Confirmation</h2>
        <div class="confirmation-message">
            <i class="fas fa-check-circle"></i>
            <p>Thank you for your order!</p>
        </div>
        <p>Your order has been placed successfully. We've sent a confirmation email to <strong><?php echo $order['email']; ?></strong>.</p>
    </div>

    <div class="order-details-grid">
        <div class="order-summary">
            <h3>Order Summary</h3>
            <div class="order-info">
                <p><strong>Order Number:</strong> #<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?></p>
                <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                <p><strong>Total Amount:</strong> RM<?php echo number_format($order['total_price'] + 5, 2); ?></p>
                <p><strong>Payment Method:</strong> 
                    <?php 
                    // This would come from your payment processing
                    echo "Credit Card"; // Default for this example
                    ?>
                </p>
            </div>

            <h3>Order Items</h3>
            <table class="order-items">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" width="70">
                                        <br>
                                    <?php endif; ?>
                                    <span><?php echo $item['name']; ?></span>
                                </div>
                            </td>
                            <td>RM<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td><br>
                            <td>RM<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">Subtotal</td>
                        <td>RM<?php echo number_format($order['total_price'], 2); ?></td>
                    </tr>
                    <tr>
                        <td colspan="3">Shipping</td>
                        <td>RM5.00</td>
                    </tr>
                    <tr>
                        <td colspan="3">Total</td>
                        <td>RM<?php echo number_format($order['total_price'] + 5, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="shipping-details">
            <h3>Shipping Information</h3>
            <div class="shipping-info">
                <p><strong>Name:</strong> <?php echo $order['name']; ?></p>
                <p><strong>Email:</strong> <?php echo $order['email']; ?></p>
                <p><strong>Phone:</strong> <?php echo $order['phone']; ?></p>
                <p><strong>Address:</strong> <?php echo nl2br($order['address']); ?></p>
            </div>

            <div class="next-steps">
                <h3>What's Next?</h3>
                <ul>
                    <li>You will receive an order confirmation email shortly.</li>
                    <li>We will process your order and notify you when it ships.</li>
                    <li>Estimated delivery: 3-5 business days</li>
                </ul>
                
                <div class="actions">
                    <a href="products.php" class="btn btn-secondary">Continue Shopping</a>
                    <a href="profile.php" class="btn btn-primary">View Order History</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'footer.php';
?>