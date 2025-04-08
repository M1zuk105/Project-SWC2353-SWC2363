<?php
require_once 'header.php';

$category = isset($_GET['category']) ? $_GET['category'] : null;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;

$products = getAllProducts($category, $searchQuery, $minPrice, $maxPrice);
?>

<section class="products-header">
    <link rel="stylesheet" href="styles/style.css">
    <h2><?php echo $category ? ucfirst($category) : 'All Products'; ?></h2>
    
    <!-- Search Bar -->
    <div class="search-bar">
        <form method="GET" action="products.php">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            <?php if($category): ?>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
            <?php endif; ?>
            <button type="submit">Search</button>
        </form>
    </div>
    
    <div class="filters-container">
        <!-- Category Filter -->
        <div class="category-filter">
            <a href="products.php" class="<?php echo !$category ? 'active' : ''; ?>">All</a>
            <a href="products.php?category=smartphones" class="<?php echo $category == 'smartphones' ? 'active' : ''; ?>">Smartphones</a>
            <a href="products.php?category=laptops" class="<?php echo $category == 'laptops' ? 'active' : ''; ?>">Laptops</a>
            <a href="products.php?category=smart home devices" class="<?php echo $category == 'smart home devices' ? 'active' : ''; ?>">Smart Home</a>
            <a href="products.php?category=accessories" class="<?php echo $category == 'accessories' ? 'active' : ''; ?>">Accessories</a>
            <a href="products.php?category=wearables" class="<?php echo $category == 'wearables' ? 'active' : ''; ?>">Wearables</a>
        </div>
        
        <!-- Price Range Filter -->
        <div class="price-filter">
            <form method="GET" action="products.php">
                <h4>Price Range (RM)</h4>
                <div class="price-inputs">
                    <input type="number" name="min_price" placeholder="Min" value="<?php echo $minPrice !== null ? htmlspecialchars($minPrice) : ''; ?>" min="0">
                    <span>to</span>
                    <input type="number" name="max_price" placeholder="Max" value="<?php echo $maxPrice !== null ? htmlspecialchars($maxPrice) : ''; ?>" min="0">
                </div>
                <?php if($category): ?>
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                <?php endif; ?>
                <?php if($searchQuery): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
                <?php endif; ?>
                <button type="submit">Apply</button>
                <?php if($minPrice !== null || $maxPrice !== null): ?>
                    <a href="<?php 
                        $params = $_GET;
                        unset($params['min_price']);
                        unset($params['max_price']);
                        echo 'products.php?' . http_build_query($params);
                    ?>" class="reset-filter">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</section>

<section class="products-grid">
    <?php if(count($products) > 0): ?>
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="price">RM<?php echo number_format($product['price'], 2); ?></p>
                <p class="rating">Rating: <?php echo htmlspecialchars($product['rating']); ?>/5</p>
                <a href="product_details.php?id=<?php echo $product['product_id']; ?>" class="btn">View Details</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-products">
            <p>No products found matching your criteria.</p>
            <a href="products.php" class="btn">Reset Filters</a>
        </div>
    <?php endif; ?>
</section>

<?php
require_once 'footer.php';
?>