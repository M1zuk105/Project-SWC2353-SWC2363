<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Locations | Zeus Tech Gadget Store</title>
    <link rel="stylesheet" href="styles/style.css">
    <style>

        .location-header {
            position: relative;
            height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            overflow: hidden;
        }
        
        .header-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            background: 
                linear-gradient(rgba(50, 50, 50, 0.7), rgba(30, 30, 30, 0.8)),
                url('image-locations.jpg') center/cover no-repeat;
        }
        
        .header-content {
            position: relative;
            z-index: 2;
            padding: 0 2rem;
            max-width: 800px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }
        
        
        .location-card {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            border: 1px solid #eee;
        }
        
    
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
        <section class="location-header">
            <div class="header-bg"></div>
            <div class="header-content">
                <h1>Our Store Locations</h1>
                <p class="lead">Visit us for hands-on experience with our latest gadgets</p>
            </div>
        </section>
        
        <div class="locations-container">
            <article class="location-card">
                <h2>Zeus Tech Headquarters</h2>
                
                <div class="map-container">
                    <!-- Google Maps Embed -->
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3972.234703835251!2d101.12345678901234!3d4.567890123456789!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNMKwMzQnMDQuNCJOIDEwMcKwMDcnMjEuNiJF!5e0!3m2!1sen!2smy!4v1234567890123!5m2!1sen!2smy" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                
                <div class="location-info">
                    <div>
                        <h3>Address</h3>
                        <address>
                            123 Tech Street,<br>
                            Ipoh Garden South,<br>
                            31400 Ipoh,<br>
                            Perak, Malaysia
                        </address>
                        
                        <h3>Contact</h3>
                        <p>
                            Phone: <a href="tel:+601155033177">+60 11-5503 3177</a><br>
                            Email: <a href="mailto:zeustechgadgetstore@gmail.com">zeustechgadgetstore@gmail.com</a>
                        </p>
                    </div>
                    
                    <div class="location-hours">
                        <h3>Operating Hours</h3>
                        <ul>
                            <li><strong>Monday-Friday:</strong> 9:00 AM - 7:00 PM</li>
                            <li><strong>Saturday:</strong> 10:00 AM - 6:00 PM</li>
                            <li><strong>Sunday:</strong> 11:00 AM - 5:00 PM</li>
                            <li><strong>Public Holidays:</strong> Closed</li>
                        </ul>
                    </div>
                </div>
            </article>
            
            <article class="location-card">
                <h2>Getting Here</h2>
                <h3>By Car</h3>
                <p>Free parking available behind our building. Look for the Zeus Tech signage.</p>
                
                <h3>By Public Transport</h3>
                <p>Nearest bus stop: Ipoh Garden South Terminal (5-minute walk)</p>
                
                <h3>Accessibility</h3>
                <p>Our store is wheelchair accessible with ramps and wide aisles.</p>
            </article>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>