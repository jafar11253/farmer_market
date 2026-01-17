<?php
include "../../includes/config.php";
require_login();

if ($_SESSION['role'] !== 'buyer') {
    header("Location: /farmer_market/index.php");
    exit;
}

if (empty($_GET['id'])) {
    die("Product ID is required.");
}

$id = (int)$_GET['id'];

// Fetch product with farmer info
$sql = "SELECT l.*, u.username AS farmer_name, u.district AS farmer_district
        FROM listings l
        JOIN users u ON u.id = l.user_id
        WHERE l.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

// Handle Add to Cart for fixed price products
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product['type'] === 'fixed') {
    $qty = (int)$_POST['quantity'];
    if ($qty < 1) $qty = 1;

    $buyer_id = $_SESSION['user_id'];

    // Insert into cart
    $stmt2 = $conn->prepare("INSERT INTO cart (user_id, listing_id, quantity) VALUES (?, ?, ?)");
    $stmt2->bind_param("iii", $buyer_id, $id, $qty);
    $stmt2->execute();

    header("Location: cart.php");
    exit;
}

// Decode images JSON
$imgPath = null;
if (!empty($product['images'])) {
    $decoded = json_decode($product['images'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded)) {
        $imgPath = $decoded[0];
    } else {
        $imgPath = $product['images'];
    }
}

include "../../includes/header.php";
?>
<h2><?php echo htmlspecialchars($product['title']); ?></h2>

<?php if ($imgPath): ?>
    <img src="/farmer_market/<?php echo htmlspecialchars($imgPath); ?>"
         alt="<?php echo htmlspecialchars($product['title']); ?>"
         style="max-width:300px; display:block; margin-bottom:10px;">
<?php endif; ?>

<p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
<p>Farmer: <?php echo htmlspecialchars($product['farmer_name']); ?>
    (<?php echo htmlspecialchars($product['farmer_district']); ?>)</p>
<p>Available Quantity: <?php echo (int)$product['quantity']; ?></p>

<?php if ($product['type'] === 'fixed'): ?>
    <p>Price: BDT <?php echo number_format($product['price'], 2); ?></p>

    <form method="POST">
        <label>Quantity</label>
        <input type="number" name="quantity" value="1" min="1" max="<?php echo (int)$product['quantity']; ?>">
        <button type="submit">Add to Cart</button>
    </form>
<?php else: ?>
    <p>This is an auction product.</p>
    <p>Starting Bid: BDT <?php echo number_format($product['starting_bid'], 2); ?></p>
    <p>Auction Ends: <?php echo htmlspecialchars($product['auction_end']); ?></p>
    <p>
        <a href="bid.php?id=<?php echo $product['id']; ?>">Go to Bidding Page</a>
    </p>
<?php endif; ?>

<?php include "../../includes/footer.php"; ?>
