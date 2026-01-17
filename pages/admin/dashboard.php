<?php
// pages/admin/dashboard.php
include "../../includes/config.php";
require_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: /FARMER_MARKET/index.php");
    exit;
}

/* ---------- STATISTICS ---------- */
$res = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='farmer'");
$total_farmers = $res->fetch_assoc()['c'] ?? 0;

$res = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='buyer'");
$total_buyers = $res->fetch_assoc()['c'] ?? 0;

$res = $conn->query("SELECT COUNT(*) AS c FROM listings WHERE status='active'");
$active_products = $res->fetch_assoc()['c'] ?? 0;

$res = $conn->query("SELECT IFNULL(SUM(commission_deducted),0) AS total FROM orders WHERE payment_status='paid'");
$total_commission = $res->fetch_assoc()['total'] ?? 0;

/* ---------- RECENT ORDERS ---------- */
$recent_sql = "SELECT o.*, u.username AS buyer_name, l.title AS listing_title
               FROM orders o
               JOIN users u ON u.id = o.user_id
               JOIN listings l ON l.id = o.listing_id
               ORDER BY o.created_at DESC
               LIMIT 5";
$recent_orders = $conn->query($recent_sql);

/* ---------- PLATFORM COMMISSION ---------- */
$rate_res = $conn->query("SELECT value FROM config WHERE key_name='commission_rate' LIMIT 1");
$rate = $rate_res && $rate_res->num_rows ? $rate_res->fetch_assoc()['value'] : "5";

include "../../includes/header.php";
?>

<!-- Inject Professional Admin Dashboard Styles -->
<style>
.admin-cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin: 2rem 0;
}

@media (max-width: 1024px) {
    .admin-cards {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .admin-cards {
        grid-template-columns: 1fr;
    }
}
</style>

<h1 class="admin-title">Admin Dashboard</h1>

<!-- Top statistic cards -->
<div class="admin-cards">
    <div class="admin-card">
        <div class="admin-card-number"><?php echo (int)$total_farmers; ?></div>
        <div class="admin-card-label">Total Farmers</div>
    </div>

    <div class="admin-card">
        <div class="admin-card-number"><?php echo (int)$total_buyers; ?></div>
        <div class="admin-card-label">Total Buyers</div>
    </div>

    <div class="admin-card">
        <div class="admin-card-number"><?php echo (int)$active_products; ?></div>
        <div class="admin-card-label">Active Products</div>
    </div>

    <div class="admin-card">
        <div class="admin-card-number">
            BDT <?php echo number_format($total_commission, 2); ?>
        </div>
        <div class="admin-card-label">Total Commission Earned</div>
    </div>
</div>

<!-- Platform commission section -->
<div class="admin-section">
    <h2>Platform Commission Rate</h2>
    <p style="font-size:1.1rem;">Current commission rate: 
        <strong style="color:var(--admin-secondary); font-size:1.3rem;"><?php echo htmlspecialchars($rate); ?>%</strong>
    </p>
    <p style="margin-top:20px;">
        <a class="admin-btn" href="/FARMER_MARKET/pages/admin/config_commission.php">
            Change Commission Percentage
        </a>
    </p>
</div>

<!-- Recent orders section -->
<div class="admin-section">
    <h2>Recent Orders</h2>

    <?php if ($recent_orders->num_rows === 0): ?>
        <p style="color:#95a5a6; font-style:italic;">No orders have been placed yet.</p>
    <?php else: ?>
        <table class="table">
            <tr>
                <th>Product</th>
                <th>Buyer</th>
                <th>Total (BDT)</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Created At</th>
            </tr>
            <?php while ($o = $recent_orders->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($o['listing_title']); ?></td>
                    <td><?php echo htmlspecialchars($o['buyer_name']); ?></td>
                    <td><strong><?php echo number_format($o['total_price'], 2); ?></strong></td>
                    <td>
                        <span class="badge badge-status-<?php echo strtolower($o['status']); ?>">
                            <?php echo htmlspecialchars(ucfirst($o['status'])); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-payment-<?php echo strtolower($o['payment_status']); ?>">
                            <?php echo htmlspecialchars(ucfirst($o['payment_status'])); ?>
                        </span>
                    </td>
                    <td><?php echo date('d M Y, h:i A', strtotime($o['created_at'])); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</div>

<!-- Manage users section -->
<div class="admin-section">
    <h2>Manage Users</h2>
    <p>
        <a class="admin-btn" href="/FARMER_MARKET/pages/admin/users.php">
            View / Manage Farmers & Buyers
        </a>
    </p>
</div>

<?php include "../../includes/footer.php"; ?>