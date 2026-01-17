<?php include "includes/config.php"; ?>
<?php include "includes/header.php"; ?>

<!-- Custom Hero & Homepage Styles -->
<style>
    :root {
        --primary: #27ae60;
        --primary-dark: #219653;
        --secondary: #f39c12;
        --light: #f8f9fa;
        --dark: #2c3e50;
        --gray: #7f8c8d;
    }

    body { font-family: 'Poppins', 'Segoe UI', sans-serif; margin:0; background:#f8f9fa; }

    /* Hero Section with Fresh Food Background */
    .hero-section {
        position: relative;
        min-height: 90vh;
        background: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.65)),
                    url('/FARMER_MARKET/assets/img/uploads/organic_food.jpg') center/cover no-repeat;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        /* padding: 2rem; */
    }

    .hero-content {
        max-width: 900px;
        animation: fadeInUp 1.2s ease-out;
    }

    .hero-title {
        font-size: 3.8rem;
        font-weight: 800;
        margin-bottom: 1rem;
        text-shadow: 0 3px 10px rgba(0,0,0,0.5);
        line-height: 1.2;
    }

    .hero-subtitle {
        font-size: 1.4rem;
        margin-bottom: 2.5rem;
        opacity: 0.95;
        font-weight: 300;
    }

    .hero-btn-group {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 2rem;
    }

    .btn-hero {
        padding: 16px 36px;
        font-size: 1.2rem;
        font-weight: 600;
        border-radius: 50px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.4s ease;
        min-width: 220px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    }

    .btn-farmer {
        background: var(--primary);
        color: white;
    }

    .btn-buyer {
        background: white;
        color: var(--primary-dark);
        border: 3px solid var(--primary);
    }

    .btn-hero:hover {
        transform: translateY(-8px) scale(1.05);
        box-shadow: 0 15px 30px rgba(0,0,0,0.35);
    }

    .btn-farmer:hover { background: var(--primary-dark); }
    .btn-buyer:hover { background: #f1f8e9; }

    /* Features Section */
    .features {
        padding: 80px 20px;
        background: white;
        text-align: center;
    }

    .features h2 {
        font-size: 2.5rem;
        color: var(--primary-dark);
        margin-bottom: 50px;
    }

    .feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 40px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .feature-card {
        padding: 30px;
        border-radius: 20px;
        background: #f8fff8;
        box-shadow: 0 10px 30px rgba(39,174,96,0.1);
        transition: all 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-15px);
        box-shadow: 0 20px 40px rgba(39,174,96,0.2);
    }

    .feature-icon {
        font-size: 3.5rem;
        margin-bottom: 20px;
    }

    .feature-title {
        font-size: 1.4rem;
        color: var(--primary-dark);
        margin-bottom: 15px;
        font-weight: 600;
    }

    /* Trust Badges */
    .trust-badges {
        padding: 60px 20px;
        background: #f0f8f0;
        text-align: center;
    }

    .badges {
        display: flex;
        justify-content: center;
        gap: 60px;
        flex-wrap: wrap;
        font-size: 1.1rem;
        color: var(--primary-dark);
        font-weight: 500;
    }

    .badge-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    main.container{
         padding: 0;
    }


    .badge-item i { font-size: 2rem; color: var(--primary); }

    /* Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(50px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        .hero-title { font-size: 2.8rem; }
        .hero-subtitle { font-size: 1.2rem; }
        .hero-btn-group { flex-direction: column; align-items: center; }
        .btn-hero { min-width: 280px; }
    }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <h1 class="hero-title">Welcome to Farmer Market</h1>
        <p class="hero-subtitle">
            Buy fresh, organic produce directly from local farmers<br>
            No middlemen • Fair prices • Delivered to your door
        </p>

        <?php if (empty($_SESSION['role'])): ?>
            <div class="hero-btn-group">
                <a href="/FARMER_MARKET/register.php?role=farmer" class="btn-hero btn-farmer">
                    Become a Farmer or Buyer
                </a>
                <a href="/FARMER_MARKET/register.php?role=buyer" class="btn-hero btn-buyer">
                    Shop Fresh Products
                </a>
            </div>
        <?php elseif ($_SESSION['role'] === 'farmer'): ?>
            <div class="hero-btn-group">
                <a href="/FARMER_MARKET/pages/farmer/dashboard.php" class="btn-hero btn-farmer">
                    Go to My Farm Dashboard
                </a>
            </div>
        <?php elseif ($_SESSION['role'] === 'buyer'): ?>
            <div class="hero-btn-group">
                <a href="/FARMER_MARKET/pages/buyer/browse.php" class="btn-hero btn-farmer">
                    Start Shopping Now
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <h2>Why Choose Farmer Market?</h2>
    <div class="feature-grid">
        <div class="feature-card">
            <div class="feature-icon">Fresh Produce</div>
            <h3 class="feature-title">100% Fresh & Organic</h3>
            <p>Get farm-fresh vegetables, fruits, dairy & more – harvested just for you.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">Direct Delivery</div>
            <h3 class="feature-title">Direct From Farmers</h3>
            <p>No middlemen. Farmers earn more, you pay less.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">Secure Payment</div>
            <h3 class="feature-title">Safe & Secure Payment</h3>
            <p>Pay online with bKash, Nagad, or card. Money held until delivery.</p>
        </div>
    </div>
</section>

<!-- Trust Badges -->
<section class="trust-badges">
    <div class="badges">
        <div class="badge-item">
            <span>Farm Fresh</span>
        </div>
        <div class="badge-item">
            <span>Local Farmers</span>
        </div>
        <div class="badge-item">
            <span>Secure Payment</span>
        </div>
        <div class="badge-item">
            <span>Fast Delivery</span>
        </div>
    </div>
</section>

<!-- Add FontAwesome for Icons (if not already in header) -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<?php include "includes/footer.php"; ?>