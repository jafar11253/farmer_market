<?php
include '../../includes/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer') header('Location: ../../login.php');
include '../../includes/header.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT o.*, l.title, u.username as buyer FROM orders o JOIN listings l ON o.listing_id = l.id JOIN users u ON o.user_id = u.id WHERE l.user_id = $user_id";
$result = $conn->query($sql);

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
?>

<style>
    .sales_container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .sales_title {
        color: #2c5f2d;
        font-size: 2em;
        margin-bottom: 30px;
        text-align: center;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }

    .sales_icon {
        font-size: 1.2em;
    }

    .sales_grid {
        display: grid;
        gap: 20px;
    }

    .sales_product {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: 2px solid #e8f5e9;
        transition: all 0.3s ease;
    }

    .sales_product:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(44, 95, 45, 0.2);
        border-color: #a5d6a7;
    }

    .sales_product_header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e8f5e9;
    }

    .sales_product_title {
        color: #2c5f2d;
        font-size: 1.4em;
        font-weight: 600;
        margin: 0;
        flex: 1;
    }

    .sales_status_badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .sales_status_pending {
        background: #fff9c4;
        color: #f57f17;
    }

    .sales_status_shipped {
        background: #bbdefb;
        color: #0d47a1;
    }

    .sales_status_delivered {
        background: #c8e6c9;
        color: #1b5e20;
    }

    .sales_product_info {
        margin: 12px 0;
        font-size: 1em;
        color: #555;
    }

    .sales_label {
        font-weight: 600;
        color: #2c5f2d;
        display: inline-block;
        min-width: 80px;
    }

    .sales_buyer {
        color: #667eea;
        font-weight: 600;
    }

    .sales_earnings {
        background: linear-gradient(135deg, #c8e6c9 0%, #e8f5e9 100%);
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
        border-left: 4px solid #2c5f2d;
    }

    .sales_earnings_amount {
        color: #2c5f2d;
        font-size: 1.5em;
        font-weight: 700;
        margin: 5px 0;
    }

    .sales_commission {
        color: #666;
        font-size: 0.9em;
    }

    .sales_update_form {
        display: flex;
        gap: 12px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid #e8f5e9;
        flex-wrap: wrap;
    }

    .sales_select {
        flex: 1;
        min-width: 200px;
        padding: 12px 15px;
        border: 2px solid #a5d6a7;
        border-radius: 8px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 1em;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .sales_select:focus {
        outline: none;
        border-color: #2c5f2d;
        box-shadow: 0 0 0 3px rgba(44, 95, 45, 0.1);
    }

    .sales_button {
        background: linear-gradient(135deg, #2c5f2d 0%, #4a8f4b 100%);
        color: white;
        border: none;
        padding: 12px 25px;
        font-size: 1em;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(44, 95, 45, 0.3);
    }

    .sales_button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(44, 95, 45, 0.5);
    }

    .sales_button:active {
        transform: translateY(0);
    }

    .sales_review_link {
        display: inline-block;
        background: #667eea;
        color: white;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95em;
        transition: all 0.3s ease;
        margin-top: 15px;
    }

    .sales_review_link:hover {
        background: #764ba2;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .sales_empty {
        text-align: center;
        padding: 60px 20px;
        background: #f9fdf9;
        border-radius: 12px;
        border: 2px dashed #a5d6a7;
        color: #666;
    }

    .sales_empty_icon {
        font-size: 5em;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .sales_empty_title {
        font-size: 1.5em;
        font-weight: 600;
        margin-bottom: 10px;
        color: #2c5f2d;
    }

    .sales_empty_text {
        font-size: 1.1em;
    }

    @media (max-width: 768px) {
        .sales_title {
            font-size: 1.6em;
        }

        .sales_product {
            padding: 20px;
        }

        .sales_product_header {
            flex-direction: column;
            gap: 15px;
        }

        .sales_product_title {
            font-size: 1.2em;
        }

        .sales_update_form {
            flex-direction: column;
        }

        .sales_select {
            min-width: 100%;
        }

        .sales_button {
            width: 100%;
        }
    }
</style>

<div class="sales_container">
    <h2 class="sales_title">
        <span class="sales_icon">💰</span>
        Sales History
    </h2>

    <?php if (count($orders) > 0): ?>
        <div class="sales_grid">
            <?php foreach ($orders as $row): ?>
                <div class="sales_product">
                    <div class="sales_product_header">
                        <h3 class="sales_product_title"><?php echo htmlspecialchars($row['title']); ?></h3>
                        <span class="sales_status_badge sales_status_<?php echo strtolower($row['status']); ?>">
                            <?php echo htmlspecialchars($row['status']); ?>
                        </span>
                    </div>

                    <p class="sales_product_info">
                        <span class="sales_label">Buyer:</span>
                        <span class="sales_buyer"><?php echo htmlspecialchars($row['buyer']); ?></span>
                    </p>

                    <div class="sales_earnings">
                        <p class="sales_earnings_amount">Earnings: $<?php echo number_format($row['total_price'], 2); ?></p>
                        <p class="sales_commission">After $<?php echo number_format($row['commission_deducted'], 2); ?> commission deducted</p>
                    </div>

                    <form method="POST" action="" class="sales_update_form">
                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                        <select name="new_status" class="sales_select">
                            <option value="pending" <?php echo $row['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="shipped" <?php echo $row['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $row['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        </select>
                        <button type="submit" name="update_status" class="sales_button">Update Status</button>
                    </form>

                    <?php if ($row['status'] == 'delivered'): ?>
                        <a href="review_buyer.php?id=<?php echo $row['id']; ?>" class="sales_review_link">⭐ Review Buyer</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="sales_empty">
            <div class="sales_empty_icon">📦</div>
            <h3 class="sales_empty_title">No Sales Yet</h3>
            <p class="sales_empty_text">Your sales will appear here once customers start purchasing your products.</p>
        </div>
    <?php endif; ?>
</div>

<?php
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    $conn->query("UPDATE orders SET status = '$new_status' WHERE id = $order_id");
    header('Location: sales.php');
    exit();
}
?>

<?php include '../../includes/footer.php'; ?>