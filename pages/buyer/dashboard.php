<?php
include "../../includes/config.php";
require_login();
if ($_SESSION['role'] !== 'buyer') { header("Location: /FARMER_MARKET/index.php"); exit; }

include "../../includes/header.php";
?>

<style>
.dashboard-header {
    text-align: center;
    margin-bottom: 3rem;
}

.dashboard-header h2 {
    font-size: 2.5rem;
    color: var(--dark);
    margin-bottom: 0.5rem;
    border-bottom: 4px solid var(--primary-color);
    display: inline-block;
    padding-bottom: 0.5rem;
}

.dashboard-header p {
    color: var(--gray);
    font-size: 1.1rem;
    margin-top: 1rem;
}

.dashboard-menu {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    margin: 2rem 0;
    padding: 0;
    list-style: none;
}

.dashboard-menu li {
    background: linear-gradient(135deg, var(--white) 0%, #f8fcf9 100%);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    transition: var(--transition);
    border: 2px solid #e8f5e9;
}

.dashboard-menu li:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-color);
}

.dashboard-menu li a {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 2.5rem 2rem;
    text-decoration: none;
    color: var(--dark);
    font-size: 1.3rem;
    font-weight: 600;
    transition: var(--transition);
}

.dashboard-menu li a:before {
    content: '';
    font-size: 2.5rem;
}

.dashboard-menu li:nth-child(1) a:before {
    content: '🛒';
}

.dashboard-menu li:nth-child(2) a:before {
    content: '🛍️';
}

.dashboard-menu li:nth-child(3) a:before {
    content: '📦';
}

.dashboard-menu li:hover a {
    color: var(--primary-color);
}

.welcome-card {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    padding: 2.5rem;
    border-radius: var(--radius-lg);
    margin-bottom: 2.5rem;
    box-shadow: var(--shadow-lg);
}

.welcome-card h3 {
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
    color: white;
}

.welcome-card p {
    font-size: 1.1rem;
    opacity: 0.95;
    margin: 0;
}

@media (max-width: 768px) {
    .dashboard-menu {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .dashboard-header h2 {
        font-size: 2rem;
    }
    
    .dashboard-menu li a {
        font-size: 1.1rem;
        padding: 2rem 1.5rem;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .dashboard-menu {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div class="welcome-card">
    <h3>🎉 Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>
    <p>Browse fresh products from local farmers and manage your orders</p>
</div>

<div class="dashboard-header">
    <h2>Buyer Dashboard</h2>
    <p>Choose an option below to get started</p>
</div>

<ul class="dashboard-menu">
    <li><a href="browse.php">Browse Products</a></li>
    <li><a href="cart.php">View Cart</a></li>
    <li><a href="orders.php">My Orders</a></li>
</ul>

<?php include "../../includes/footer.php"; ?>
