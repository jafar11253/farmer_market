<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Market - Fresh from Farm to Table</title>
    <meta name="description" content="Buy fresh, organic produce directly from local farmers">
    <link rel="stylesheet" href="/FARMER_MARKET/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="/FARMER_MARKET/assets/js/script.js" defer></script>
</head>
<body>
<header>
    <div class="header-container">
        <h1><a href="/FARMER_MARKET/index.php"><i class="fas fa-seedling"></i> Farmer Market</a></h1>
        <nav>
            <a href="/FARMER_MARKET/index.php"><i class="fas fa-home"></i> Home</a>
            <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'buyer'): ?>
                <a href="/FARMER_MARKET/pages/buyer/browse.php"><i class="fas fa-store"></i> Browse</a>
                <a href="/FARMER_MARKET/pages/buyer/cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                <a href="/FARMER_MARKET/pages/buyer/orders.php"><i class="fas fa-box-open"></i> Orders</a>
                <a href="/FARMER_MARKET/pages/buyer/dashboard.php"><i class="fas fa-user-circle"></i> Dashboard</a>
            <?php elseif (!empty($_SESSION['role']) && $_SESSION['role'] === 'farmer'): ?>
                <a href="/FARMER_MARKET/pages/farmer/dashboard.php"><i class="fas fa-tractor"></i> Dashboard</a>
                <a href="/FARMER_MARKET/pages/farmer/create_listing.php"><i class="fas fa-plus-circle"></i> Add Product</a>
                <a href="/FARMER_MARKET/pages/farmer/manage_listings.php"><i class="fas fa-list"></i> My Products</a>
                <a href="/FARMER_MARKET/pages/farmer/sales.php"><i class="fas fa-chart-line"></i> Sales</a>
            <?php elseif (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="/FARMER_MARKET/pages/admin/dashboard.php"><i class="fas fa-chart-bar"></i> Dashboard</a>
                <a href="/FARMER_MARKET/pages/admin/users.php"><i class="fas fa-users"></i> Users</a>
                <a href="/FARMER_MARKET/pages/admin/reports.php"><i class="fas fa-file-alt"></i> Reports</a>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="nav-user"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="/FARMER_MARKET/logout.php" class="nav-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="/FARMER_MARKET/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="/FARMER_MARKET/register.php" class="nav-register"><i class="fas fa-user-plus"></i> Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
