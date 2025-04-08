<?php
require_once 'header.php';
?>

<section class="hero">
    <h2>Welcome to Zeus Tech Gadget Store</h2>
    <p>Your one-stop shop for the latest gadgets and tech accessories</p>
    <a href="products.php" class="btn">Shop Now</a>
</section>

<!-- Enhanced Video Slider Section -->
<section class="video-slider">
    <div class="section-header">
        <h2>Latest arrival</h2>
        <div class="slider-nav">
            <button class="nav-btn prev" onclick="plusSlides(-1)">
                <span class="arrow">&#10094;</span>
            </button>
            <button class="nav-btn next" onclick="plusSlides(1)">
                <span class="arrow">&#10095;</span>
            </button>
        </div>
    </div>
    
    <div class="slideshow-container">
        <!-- Slide 1 -->
        <div class="mySlides fade">
            <div class="video-container">
                <iframe src="https://www.youtube.com/embed/f5vKxPA43lM?autoplay=1&mute=1&enablejsapi=1" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen
                        loading="lazy"></iframe>
            </div>
        </div>
        
        <!-- Slide 2 -->
        <div class="mySlides fade">
            <div class="video-container">
                <iframe src="https://www.youtube.com/embed/keYat4iSYAQ?autoplay=1&mute=1&enablejsapi=1" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen
                        loading="lazy"></iframe>
            </div>
        </div>
        
        <!-- Slide 3 -->
        <div class="mySlides fade">
            <div class="video-container">
                <iframe src="https://www.youtube.com/embed/ePdbj2bZ-Ro?autoplay=1&mute=1&enablejsapi=1" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen
                        loading="lazy"></iframe>
            </div>
        </div>
    </div>
    
    <!-- Enhanced Dot Indicators -->
    <div class="slider-dots">
        <span class="dot" onclick="currentSlide(1)"></span>
        <span class="dot" onclick="currentSlide(2)"></span>
        <span class="dot" onclick="currentSlide(3)"></span>
    </div>
</section>

<section class="featured-products">
    <h2>Featured Products</h2>
    <div class="products-grid">
        <?php
        $featuredProducts = getAllProducts();
        $featuredProducts = array_slice($featuredProducts, 0, 4);
        foreach ($featuredProducts as $product): ?>
            <div class="product-card">
                <img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                <h3><?php echo $product['name']; ?></h3>
                <p class="price">RM<?php echo number_format($product['price'], 2); ?></p>
                <a href="product_details.php?id=<?php echo $product['product_id']; ?>" class="btn">View Details</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<style>
    /* Enhanced Video Slider Styles */
    .video-slider {
        padding: 40px 20px;
        position: relative;
        background: #f9f9f9;
        margin: 30px 0;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 0 10px;
    }
    
    .video-slider h2 {
        color: #2c3e50;
        margin: 0;
        font-size: 1.8rem;
    }
    
    .slider-nav {
        display: flex;
        gap: 10px;
    }
    
    .nav-btn {
        cursor: pointer;
        width: 40px;
        height: 40px;
        border: none;
        background: rgba(0,0,0,0.7);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .nav-btn:hover {
        background: #2c3e50;
        transform: scale(1.1);
    }
    
    .arrow {
        font-size: 20px;
        font-weight: bold;
    }
    
    .slideshow-container {
        max-width: 900px;
        position: relative;
        margin: 0 auto;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .mySlides {
        display: none;
        transition: opacity 0.5s ease;
    }
    
    .video-container {
        position: relative;
        padding-bottom: 56.25%;
        height: 0;
        overflow: hidden;
        background: #000;
    }
    
    .video-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }
    
    .slider-dots {
        text-align: center;
        padding: 20px 0 10px;
    }
    
    .dot {
        cursor: pointer;
        height: 12px;
        width: 12px;
        margin: 0 6px;
        background-color: #bbb;
        border-radius: 50%;
        display: inline-block;
        transition: all 0.3s ease;
    }
    
    .dot:hover, .active {
        background-color: #2c3e50;
        transform: scale(1.2);
    }
    
    .fade {
        animation-name: fade;
        animation-duration: 1s;
    }
    
    @keyframes fade {
        from {opacity: .6}
        to {opacity: 1}
    }
</style>

<script>
    let slideIndex = 1;
    let slideInterval;
    const slideDuration = 6000; // 6 seconds
    
    // Initialize slider
    showSlides(slideIndex);
    startSlideShow();
    
    function plusSlides(n) {
        resetSlideTimer();
        showSlides(slideIndex += n);
    }
    
    function currentSlide(n) {
        resetSlideTimer();
        showSlides(slideIndex = n);
    }
    
    function showSlides(n) {
        let i;
        let slides = document.getElementsByClassName("mySlides");
        let dots = document.getElementsByClassName("dot");
        
        if (n > slides.length) { slideIndex = 1 }
        if (n < 1) { slideIndex = slides.length }
        
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
        
        for (i = 0; i < dots.length; i++) {
            dots[i].classList.remove("active");
        }
        
        slides[slideIndex-1].style.display = "block";
        dots[slideIndex-1].classList.add("active");
    }
    
    function startSlideShow() {
        slideInterval = setInterval(() => {
            plusSlides(1);
        }, slideDuration);
    }
    
    function resetSlideTimer() {
        clearInterval(slideInterval);
        startSlideShow();
    }
    
    // Pause on hover
    const slider = document.querySelector('.slideshow-container');
    slider.addEventListener('mouseenter', () => clearInterval(slideInterval));
    slider.addEventListener('mouseleave', startSlideShow);
</script>

<?php
require_once 'footer.php';
?>