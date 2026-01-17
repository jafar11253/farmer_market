<?php
include '../../includes/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}
include '../../includes/header.php';

// Get report data
$total_sales = $conn->query("SELECT SUM(total_price + commission_deducted) as total FROM orders")->fetch_assoc()['total'];
$total_commission = $conn->query("SELECT SUM(commission_deducted) as total FROM orders")->fetch_assoc()['total'];

// Get commission rate from config
$rate_res = $conn->query("SELECT value FROM config WHERE key_name='commission_rate' LIMIT 1");
$commission_rate = $rate_res && $rate_res->num_rows ? ($rate_res->fetch_assoc()['value'] / 100) : 0.05;

$monthly_commission = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
           SUM(commission_deducted) as commission 
    FROM orders 
    GROUP BY month 
    ORDER BY month DESC 
    LIMIT 6
");

$top_farmers = $conn->query("
    SELECT u.username, COUNT(o.id) as sales, SUM(o.total_price) as earnings
    FROM orders o 
    JOIN listings l ON o.listing_id = l.id 
    JOIN users u ON l.user_id = u.id 
    WHERE u.role = 'farmer'
    GROUP BY u.id 
    ORDER BY sales DESC 
    LIMIT 5
");
?>

<style>
    .reports_title {
        color: #ff6b6b;
        font-size: 2em;
        margin-bottom: 30px;
        text-align: center;
        font-weight: 700;
    }

    .reports_grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }

    .report_card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .report_card h3 {
        color: #333;
        margin-bottom: 15px;
        font-size: 1.3em;
    }

    .commission_item {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
    }

    .commission_item:last-child {
        border-bottom: none;
    }

    .farmer_rank {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
    }

    .farmer_rank:last-child {
        border-bottom: none;
    }

    .rank_info h4 {
        margin: 0;
        color: #333;
    }

    .rank_stats {
        text-align: right;
    }

    .rank_stats .sales {
        color: #667eea;
        font-weight: 600;
    }

    .rank_stats .earnings {
        color: #2c5f2d;
        font-size: 0.9em;
    }
</style>

<h2 class="reports_title">Platform Reports</h2>

<div class="reports_grid">
    <div class="report_card">
        <h3>💰 Financial Summary</h3>
        <div class="commission_item">
            <span>Total Sales:</span>
            <strong>$<?php echo number_format($total_sales ?? 0, 2); ?></strong>
        </div>
        <div class="commission_item">
            <span>Total Commission:</span>
            <strong style="color: #ff6b6b;">$<?php echo number_format($total_commission ?? 0, 2); ?></strong>
        </div>
        <div class="commission_item">
            <span>Commission Rate:</span>
            <strong><?php echo ($commission_rate * 100); ?>%</strong>
        </div>
    </div>

    <div class="report_card">
        <h3>📈 Monthly Commission</h3>
        <?php if ($monthly_commission->num_rows > 0): ?>
            <?php while ($month = $monthly_commission->fetch_assoc()): ?>
                <div class="commission_item">
                    <span><?php echo date('F Y', strtotime($month['month'] . '-01')); ?>:</span>
                    <strong>$<?php echo number_format($month['commission'], 2); ?></strong>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No commission data available.</p>
        <?php endif; ?>
    </div>

    <div class="report_card">
        <h3>🏆 Top Farmers</h3>
        <?php if ($top_farmers->num_rows > 0): ?>
            <?php while ($farmer = $top_farmers->fetch_assoc()): ?>
                <div class="farmer_rank">
                    <div class="rank_info">
                        <h4><?php echo htmlspecialchars($farmer['username']); ?></h4>
                    </div>
                    <div class="rank_stats">
                        <div class="sales"><?php echo $farmer['sales']; ?> sales</div>
                        <div class="earnings">$<?php echo number_format($farmer['earnings'], 2); ?> earned</div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No farmer data available.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>