<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header("Location: profile.php");
    exit();
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Get order header information
$order_query = $conn->prepare("
    SELECT o.*, u.address 
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ? AND o.user_id = ?
");
$order_query->bind_param("ii", $order_id, $user_id);
$order_query->execute();
$order_result = $order_query->get_result();

if ($order_result->num_rows === 0) {
    die("Order not found or you don't have permission to view it.");
}

$order = $order_result->fetch_assoc();

// Initialize default values if they don't exist
$order['order_status'] = $order['order_status'] ?? 'pending';
$order['order_date'] = $order['order_date'] ?? date('Y-m-d H:i:s');
$order['total_price'] = $order['total_price'] ?? 0;

// Get all order items
$items_query = $conn->prepare("
    SELECT od.*, p.name as product_name, p.image 
    FROM order_details od
    JOIN products p ON od.product_id = p.product_id
    WHERE od.order_id = ?
");
$items_query->bind_param("i", $order_id);
$items_query->execute();
$items_result = $items_query->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= htmlspecialchars($order['order_id']) ?> | Zeus Tech</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1000px;
            margin: 20px ;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .status {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
        .status-pending { background: #FFF3CD; color: #856404; }
        .status-processing { background: #CCE5FF; color: #004085; }
        .status-shipped { background: #D4EDDA; color: #155724; }
        .status-delivered { background: #D1ECF1; color: #0C5460; }
        .status-cancelled { background: #F8D7DA; color: #721C24; }
        .order-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        .order-section {
            margin-bottom: 30px;
        }
        .order-section h3 {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .product-card {
            display: flex;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .product-image {
            width: 120px;
            height: 120px;
            object-fit: contain;
            margin-right: 20px;
            border: 1px solid #eee;
        }
        .product-details {
            flex-grow: 1;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #0069d9;
        }
        .btn-cancel {
            background: #dc3545;
        }
        .btn-cancel:hover {
            background: #c82333;
        }
        .progress-tracker {
            margin: 30px 0;
        }
        .progress-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 20px;
        }
        .progress-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 4px;
            background: #e0e0e0;
            z-index: 1;
        }
        .progress-step {
            text-align: center;
            position: relative;
            z-index: 2;
        }
        .step-number {
            width: 34px;
            height: 34px;
            background: #e0e0e0;
            color: #666;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
        }
        .step-label {
            font-size: 14px;
            color: #666;
        }
        .active .step-number {
            background: #007bff;
            color: white;
        }
        .active .step-label {
            color: #007bff;
            font-weight: bold;
        }
        .completed .step-number {
            background: #28a745;
            color: white;
        }
        .completed .step-label {
            color: #28a745;
        }
        .order-total {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-weight: bold;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="order-header">
            <h1>Order #<?= htmlspecialchars($order['order_id']) ?></h1>
            <span class="status status-<?= htmlspecialchars($order['order_status']) ?>">
                <?= ucfirst(htmlspecialchars($order['order_status'])) ?>
            </span>
        </div>

        <!-- Order Progress Tracker -->
        <div class="progress-tracker">
            <div class="progress-steps">
                <div class="progress-step <?= $order['order_status'] == 'pending' ? 'active' : (in_array($order['order_status'], ['processing', 'shipped', 'delivered']) ? 'completed' : '') ?>">
                    <div class="step-number">1</div>
                    <div class="step-label">Order Placed</div>
                </div>
                <div class="progress-step <?= $order['order_status'] == 'processing' ? 'active' : (in_array($order['order_status'], ['shipped', 'delivered']) ? 'completed' : '') ?>">
                    <div class="step-number">2</div>
                    <div class="step-label">Processing</div>
                </div>
                <div class="progress-step <?= $order['order_status'] == 'shipped' ? 'active' : ($order['order_status'] == 'delivered' ? 'completed' : '') ?>">
                    <div class="step-number">3</div>
                    <div class="step-label">Shipped</div>
                </div>
                <div class="progress-step <?= $order['order_status'] == 'delivered' ? 'active' : '' ?>">
                    <div class="step-number">4</div>
                    <div class="step-label">Delivered</div>
                </div>
            </div>
        </div>

        <div class="order-grid">
            <div class="order-items">
                <div class="order-section">
                    <h3>Order Items</h3>
                    <?php foreach ($order_items as $item): ?>
                        <div class="product-card">
                            <img src="<?= htmlspecialchars($item['image'] ?? 'images/products.jpg') ?>" 
                                 alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                 class="product-image">
                            <div class="product-details">
                                <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                                <p>Unit Price: RM<?= number_format($item['price'], 2) ?></p>
                                <p>Quantity: <?= htmlspecialchars($item['quantity']) ?></p>
                                <p>Subtotal: RM<?= number_format($item['subtotal'], 2) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="order-total">
                        <p>Order Total: RM<?= number_format($order['total_price'], 2) ?></p>
                    </div>
                </div>
            </div>

            <div class="order-summary">
                <div class="order-section">
                    <h3>Order Summary</h3>
                    <p><strong>Order Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['order_date'])) ?></p>
                    <p><strong>Order Status:</strong> <?= ucfirst(htmlspecialchars($order['order_status'])) ?></p>
                    <p><strong>Items:</strong> <?= array_sum(array_column($order_items, 'quantity')) ?></p>
                    <p><strong>Shipping:</strong> RM5.00</p>
                    <p><strong>Total:</strong> RM<?= number_format($order['total_price'] + 5, 2) ?></p>
                </div>

                <div class="order-section">
                    <h3>Shipping Address</h3>
                    <p><?= nl2br(htmlspecialchars($order['address'] ?? 'No address provided')) ?></p>
                </div>
            </div>
        </div>

        <div class="actions">
            <a href="profile.php" class="btn">Back to Profile</a>
            
            <?php if ($order['order_status'] == 'pending'): ?>
                <form action="cancel_order.php" method="post" style="display: inline;">
                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['order_id']) ?>">
                    <button type="submit" class="btn btn-cancel">Cancel Order</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Simple script to animate progress bar
        document.addEventListener('DOMContentLoaded', function() {
            const progressSteps = document.querySelectorAll('.progress-step');
            let currentStep = 0;
            
            // Determine current step based on status
            const status = "<?= $order['order_status'] ?>";
            switch(status) {
                case 'pending': currentStep = 1; break;
                case 'processing': currentStep = 2; break;
                case 'shipped': currentStep = 3; break;
                case 'delivered': currentStep = 4; break;
                default: currentStep = 0;
            }
            
            // Animate progress
            if (currentStep > 0) {
                const progressBar = document.querySelector('.progress-steps');
                const percentage = ((currentStep - 1) / 3) * 100;
                
                progressBar.style.setProperty('--progress', percentage + '%');
            }
        });
    </script>
</body>
</html>