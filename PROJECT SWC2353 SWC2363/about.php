<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Zeus Tech Gadget Store</title>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        /* About Page Specific Styles */
        .about-header {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
        }
        
        .about-content {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .mission-vision {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .team-section {
            margin: 4rem 0;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
        <section class="about-header">
            <h1>About Zeus Tech</h1>
            <p class="lead">Your trusted partner in cutting-edge technology since 2020</p>
        </section>
        
        <section class="about-content">
            <article class="card">
                <h2>Our Story</h2>
                <p>Founded in 2020, Zeus Tech Gadget Store began as a small tech shop in Ipoh, Perak with a big vision - to make premium gadgets accessible to everyone. What started as a humble storefront has now grown into one of Malaysia's leading online tech retailers.</p>
                
                <p>Named after the Greek god of thunder, we deliver powerful tech solutions that supercharge your digital life. Our carefully curated selection includes only the most innovative and reliable products on the market.</p>
            </article>
            
            <div class="mission-vision">
                <article class="card">
                    <h3>Our Mission</h3>
                    <p>To bridge the gap between cutting-edge technology and everyday users by providing high-quality gadgets with exceptional customer service at competitive prices.</p>
                </article>
                
                <article class="card">
                    <h3>Our Vision</h3>
                    <p>To become Malaysia's most trusted tech retailer by 2025, known for our product expertise, fast delivery, and outstanding after-sales support.</p>
                </article>
            </div>
            
            <article class="card">
                <h2>Why Choose Zeus Tech?</h2>
                <ul>
                    <li><strong>Authentic Products:</strong> 100% genuine products with manufacturer warranties</li>
                    <li><strong>Tech Experts:</strong> Our team provides knowledgeable recommendations</li>
                    <li><strong>Fast Shipping:</strong> Next-day delivery across Peninsular Malaysia</li>
                    <li><strong>Easy Returns:</strong> 7-day hassle-free return policy</li>
                    <li><strong>Competitive Prices:</strong> Best price guarantee for all products</li>
                </ul>
            </article>
            
            <section class="team-section">
                <h2>Meet Our Leadership</h2>
                <div class="team-grid">
                    <div class="card">
                        <h3>Firdaus</h3>
                        <p><em>Founder & CEO</em></p>
                        <p>Former engineer with 10+ years in consumer electronics</p>
                    </div>
                    <div class="card">
                        <h3>Sarah Lim</h3>
                        <p><em>Head of Operations</em></p>
                        <p>Specializes in logistics and supply chain management</p>
                    </div>
                    <div class="card">
                        <h3>Adam</h3>
                        <p><em>Tech Director</em></p>
                        <p>Gadget expert and product reviewer</p>
                    </div>
                </div>
            </section>
            
            <article class="card">
                <h2>Visit Us</h2>
                <p><strong>Headquarters:</strong><br>
                123 Tech Street, Ipoh Garden South,<br>
                31400 Ipoh, Perak, Malaysia</p>
                
                <p><strong>Operating Hours:</strong><br>
                Monday-Friday: 9am-7pm<br>
                Saturday-Sunday: 10am-5pm</p>
                
                <p><strong>Contact:</strong><br>
                Phone: +6011-5503 3177<br>
                Email: zeustechgadgetstore@gmail.com</p>
            </article>
        </section>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>