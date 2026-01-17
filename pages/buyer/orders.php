<?php
// pages/buyer/orders.php

include "../../includes/config.php";
require_login();

// Only buyers can access this page
if ($_SESSION['role'] !== 'buyer') {
    header("Location: /farmer_market/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all orders for this buyer, join with listings for title/type
$sql = "SELECT o.*, l.title AS listing_title, l.type AS listing_type
        FROM orders o
        LEFT JOIN listings l ON l.id = o.listing_id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

include "../../includes/header.php";
?>

<h2>My Orders</h2>

<?php if ($res->num_rows === 0): ?>
    <p>You have no orders yet.</p>
<?php else: ?>
    <table class="table">
        <tr>
            <th>Order ID</th>
            <th>Product</th>
            <th>Type</th>
            <th>Quantity</th>
            <th>Total Price (BDT)</th>
            <th>Status</th>
            <th>Payment</th>
            <th>Created At</th>
        </tr>
        <?php while ($o = $res->fetch_assoc()): ?>
            <tr>
                <td><?php echo $o['id']; ?></td>
                <td>
                    <?php echo htmlspecialchars($o['listing_title'] ?? 'N/A'); ?>
                </td>
                <td>
                    <?php echo htmlspecialchars($o['listing_type'] ?? 'N/A'); ?>
                </td>
                <td>
                    <?php echo (int)$o['quantity']; ?>
                </td>
                <td>
                    <?php echo number_format($o['total_price'], 2); ?>
                </td>
                <td>
                    <?php echo htmlspecialchars($o['status']); ?>
                </td>
                <td>
                    <?php echo htmlspecialchars($o['payment_status']); ?>
                    <?php if ($o['payment_status'] === 'pending'): ?>
                        <br>
                        <a href="checkout.php?order_id=<?php echo $o['id']; ?>">
                            Pay Now
                        </a>
                    <?php endif; ?>
                </td>
                <td>
                    <?php echo $o['created_at']; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php endif; ?>

<?php include "../../includes/footer.php"; ?>
