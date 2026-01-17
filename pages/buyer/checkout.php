<?php
include "../../includes/config.php";
require_login();

// Only buyers allowed
if ($_SESSION['role'] !== 'buyer') {
    header("Location: /farmer_market/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = "";

/* ----------------------------------------------------
   1) GET COMMISSION RATE
---------------------------------------------------- */
$rate_res = $conn->query("SELECT value FROM config WHERE key_name='commission_rate' LIMIT 1");
$rate_row = $rate_res ? $rate_res->fetch_assoc() : null;
$commission_rate = $rate_row ? (float)$rate_row['value'] : 5.0;

/* ----------------------------------------------------
   2) CHECKOUT FROM CART (POST)
---------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['order_id'])) {

    $sql = "SELECT cart.listing_id, cart.quantity, listings.price
            FROM cart
            JOIN listings ON listings.id = cart.listing_id
            WHERE cart.user_id = ? AND listings.type='fixed'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $msg = "Your cart is empty.";
    } else {
        $listing_id_for_order = null;
        $total = 0;
        $total_qty = 0;

        while ($row = $res->fetch_assoc()) {
            if ($listing_id_for_order === null) {
                $listing_id_for_order = (int)$row['listing_id'];
            }
            $qty = (int)$row['quantity'];
            $price = (float)$row['price'];

            $total += $price * $qty;
            $total_qty += $qty;
        }

        if ($total_qty <= 0) {
            $total_qty = 1;
        }

        $commission = round($total * $commission_rate / 100, 2);

        $stmt2 = $conn->prepare(
            "INSERT INTO orders 
            (listing_id, user_id, quantity, total_price, status, commission_deducted, payment_status)
            VALUES (?, ?, ?, ?, 'pending', ?, 'pending')"
        );
        $stmt2->bind_param("iiidd", $listing_id_for_order, $user_id, $total_qty, $total, $commission);

        if ($stmt2->execute()) {
            $order_id = $stmt2->insert_id;

            // Clear cart
            $conn->query("DELETE FROM cart WHERE user_id = $user_id");

            header("Location: checkout.php?order_id=$order_id");
            exit;
        } else {
            $msg = "Error creating order: " . $stmt2->error;
        }
    }
}

/* ----------------------------------------------------
   3) HANDLE PAYMENT RESPONSE (?order_id=..&status=..)
---------------------------------------------------- */
if (isset($_GET['order_id']) && isset($_GET['status'])) {

    $order_id = (int)$_GET['order_id'];
    $status = $_GET['status'];

    $check = $conn->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
    $check->bind_param("ii", $order_id, $user_id);
    $check->execute();
    $order = $check->get_result()->fetch_assoc();

    if (!$order) {
        $msg = "Order not found.";
    } else {
        if ($status === "success") {

            // VALID VALUES BASED ON YOUR DATABASE:
            // status = 'delivered'
            // payment_status = 'paid'

            $stmt = $conn->prepare("UPDATE orders 
                                    SET payment_status='paid', status='delivered'
                                    WHERE id=?");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();

            $msg = "Payment successful! Order marked as delivered.";

        } elseif ($status === "failed") {

            $stmt = $conn->prepare("UPDATE orders 
                                    SET payment_status='failed'
                                    WHERE id=?");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();

            $msg = "Payment failed.";
        }
    }
}

/* ----------------------------------------------------
   4) VIEW EXISTING ORDER (?order_id=..)
---------------------------------------------------- */
$current_order = null;

if (isset($_GET['order_id']) && !isset($_GET['status'])) {
    $order_id = (int)$_GET['order_id'];

    $stmt = $conn->prepare(
        "SELECT o.*, l.title AS listing_title
         FROM orders o
         LEFT JOIN listings l ON l.id = o.listing_id
         WHERE o.id=? AND o.user_id=?"
    );
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $current_order = $stmt->get_result()->fetch_assoc();

    if (!$current_order) {
        $msg = "Order not found.";
    }
}

include "../../includes/header.php";
?>

<h2>Checkout</h2>

<?php if ($msg): ?>
    <p><?php echo htmlspecialchars($msg); ?></p>
<?php endif; ?>

<?php if ($current_order): ?>
    <h3>Order Summary</h3>
    <p><strong>Order ID:</strong> <?php echo $current_order['id']; ?></p>
    <p><strong>Product:</strong> <?php echo htmlspecialchars($current_order['listing_title']); ?></p>
    <p><strong>Quantity:</strong> <?php echo $current_order['quantity']; ?></p>
    <p><strong>Total:</strong> BDT <?php echo number_format($current_order['total_price'], 2); ?></p>
    <p><strong>Status:</strong> <?php echo $current_order['status']; ?></p>
    <p><strong>Payment Status:</strong> <?php echo $current_order['payment_status']; ?></p>

    <?php if ($current_order['payment_status'] === 'pending'): ?>
        <h3>Simulated Payment</h3>

        <p>
            ✔ <a href="checkout.php?order_id=<?php echo $current_order['id']; ?>&status=success">
                Complete Payment (Success)
               </a>
        </p>
        <p>
            ❌ <a href="checkout.php?order_id=<?php echo $current_order['id']; ?>&status=failed">
                Fail Payment
               </a>
        </p>
    <?php endif; ?>

<?php else: ?>
    <p>No order selected.  
       Go to <a href="orders.php">My Orders</a>.</p>
<?php endif; ?>

<?php include "../../includes/footer.php"; ?>
