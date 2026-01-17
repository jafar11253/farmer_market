<?php
// pages/buyer/cart.php

include "../../includes/config.php";
require_login();

// Only buyers can access this page
if ($_SESSION['role'] !== 'buyer') {
    header("Location: /farmer_market/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = "";

/*
    1) Handle remove-from-cart action
*/
if (isset($_GET['remove_id'])) {
    $remove_id = (int)$_GET['remove_id'];

    // Ensure this cart row belongs to this user
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $remove_id, $user_id);

    if ($stmt->execute()) {
        $msg = "Item removed from cart.";
    } else {
        $msg = "Error removing item: " . $stmt->error;
    }
}

/*
    2) Handle quantity update (optional but useful)
       Form: POST with fields like quantity[cart_id] = new_qty
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (!empty($_POST['quantity']) && is_array($_POST['quantity'])) {
        foreach ($_POST['quantity'] as $cart_id => $qty) {
            $cart_id = (int)$cart_id;
            $qty     = (int)$qty;
            if ($qty < 1) $qty = 1;

            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("iii", $qty, $cart_id, $user_id);
            $stmt->execute();
        }
        $msg = "Cart updated.";
    }
}

/*
    3) Fetch cart items for this buyer
*/
$sql = "SELECT cart.id AS cart_id,
               cart.quantity,
               listings.id AS listing_id,
               listings.title,
               listings.price,
               listings.type
        FROM cart
        JOIN listings ON listings.id = cart.listing_id
        WHERE cart.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$cart_items = [];
$grand_total = 0;

while ($row = $res->fetch_assoc()) {
    // Only fixed-price items should be in the cart
    if ($row['type'] === 'fixed') {
        $subtotal = $row['price'] * $row['quantity'];
        $row['subtotal'] = $subtotal;
        $grand_total += $subtotal;
        $cart_items[] = $row;
    }
}

include "../../includes/header.php";
?>

<style>
    .cart-container {
        max-width: 1000px;
        margin: 2rem auto;
        padding: 2rem;
    }

    .cart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        border-bottom: 3px solid var(--primary-color);
        padding-bottom: 1rem;
    }

    .cart-header h2 {
        margin: 0;
        font-size: 2.2rem;
        color: var(--dark);
        border: none;
    }

    .cart-badge {
        background: var(--primary-color);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        border-left: 4px solid #17a2b8;
    }

    .cart-empty {
        text-align: center;
        padding: 3rem 2rem;
        background: #f8f9fa;
        border-radius: 12px;
        color: var(--gray);
    }

    .cart-empty i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .cart-items-wrapper {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow-md);
        margin-bottom: 2rem;
    }

    .cart-item {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 0.8fr;
        gap: 1.5rem;
        padding: 1.5rem;
        align-items: center;
        border-bottom: 1px solid #ecf0f1;
        transition: background 0.3s ease;
    }

    .cart-item:hover {
        background: #f8f9fa;
    }

    .cart-item:last-child {
        border-bottom: none;
    }

    .product-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .product-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-light), var(--primary-color));
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .product-name {
        font-weight: 600;
        color: var(--dark);
        font-size: 1.05rem;
    }

    .quantity-input {
        width: 80px;
        padding: 0.6rem;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        text-align: center;
        font-weight: 600;
        transition: border 0.3s;
    }

    .quantity-input:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .price {
        font-weight: 600;
        color: var(--primary-color);
        font-size: 1.1rem;
    }

    .remove-btn {
        background: #ff6b6b;
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
    }

    .remove-btn:hover {
        background: #ff5252;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
    }

    .cart-summary {
        background: linear-gradient(135deg, var(--primary-light), var(--primary-color));
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-md);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        font-size: 1.05rem;
    }

    .summary-row.total {
        border-top: 2px solid rgba(255, 255, 255, 0.3);
        padding-top: 1rem;
        font-size: 1.4rem;
        font-weight: 700;
    }

    .cart-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn {
        flex: 1;
        min-width: 200px;
        padding: 1rem 2rem;
        border: none;
        border-radius: 8px;
        font-size: 1.05rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
    }

    .btn-update {
        background: #667eea;
        color: white;
    }

    .btn-update:hover {
        background: #5568d3;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }

    .btn-checkout {
        background: var(--primary-color);
        color: white;
        flex: 2;
    }

    .btn-checkout:hover {
        background: var(--primary-dark);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(39, 174, 96, 0.3);
    }

    .column-header {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 0.8fr;
        gap: 1.5rem;
        padding: 1.5rem;
        background: #f8f9fa;
        font-weight: 700;
        color: var(--dark);
        border-bottom: 2px solid var(--primary-color);
    }

    @media (max-width: 768px) {
        .cart-item,
        .column-header {
            grid-template-columns: 1fr;
        }

        .cart-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .cart-actions {
            flex-direction: column;
        }

        .btn {
            min-width: 100%;
        }
    }
</style>

<div class="cart-container">
    <div class="cart-header">
        <h2><i class="fas fa-shopping-cart"></i> My Cart</h2>
        <span class="cart-badge"><i class="fas fa-box"></i> <?php echo count($cart_items); ?> Item<?php echo count($cart_items) !== 1 ? 's' : ''; ?></span>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <div class="cart-empty">
            <i class="fas fa-shopping-bag"></i>
            <h3>Your cart is empty</h3>
            <p>Start shopping to add items to your cart!</p>
            <a href="../buyer/browse.php" class="btn btn-checkout" style="margin-top: 1.5rem;">
                <i class="fas fa-store"></i> Continue Shopping
            </a>
        </div>
    <?php else: ?>

        <div class="cart-items-wrapper">
            <div class="column-header">
                <div><i class="fas fa-box-open"></i> Product</div>
                <div><i class="fas fa-tag"></i> Price</div>
                <div><i class="fas fa-cube"></i> Quantity</div>
                <div><i class="fas fa-calculator"></i> Subtotal</div>
                <div><i class="fas fa-trash"></i> Action</div>
            </div>

            <form method="POST">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="product-info">
                            <div class="product-icon">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <div class="product-name"><?php echo htmlspecialchars($item['title']); ?></div>
                        </div>
                        <div class="price">BDT <?php echo number_format($item['price'], 2); ?></div>
                        <input type="number"
                               class="quantity-input"
                               name="quantity[<?php echo $item['cart_id']; ?>]"
                               value="<?php echo (int)$item['quantity']; ?>"
                               min="1">
                        <div class="price">BDT <?php echo number_format($item['subtotal'], 2); ?></div>
                        <a href="cart.php?remove_id=<?php echo $item['cart_id']; ?>"
                           class="remove-btn"
                           onclick="return confirm('Remove this item from cart?');">
                            <i class="fas fa-trash-alt"></i> Remove
                        </a>
                    </div>
                <?php endforeach; ?>
            </form>
        </div>

        <div class="cart-summary">
            <div class="summary-row">
                <span><i class="fas fa-cube"></i> Total Items:</span>
                <span><?php echo array_reduce($cart_items, fn($sum, $item) => $sum + $item['quantity'], 0); ?></span>
            </div>
            <div class="summary-row total">
                <span><i class="fas fa-money-bill-wave"></i> Grand Total:</span>
                <span>BDT <?php echo number_format($grand_total, 2); ?></span>
            </div>
        </div>

        <div class="cart-actions">
            <form method="POST" style="flex: 1;">
                <button type="submit" name="update_cart" class="btn btn-update">
                    <i class="fas fa-sync-alt"></i> Update Cart
                </button>
            </form>

            <form method="POST" action="checkout.php" style="flex: 2;">
                <button type="submit" class="btn btn-checkout">
                    <i class="fas fa-credit-card"></i> Proceed to Checkout
                </button>
            </form>
        </div>

    <?php endif; ?>
</div>

<?php include "../../includes/footer.php"; ?>
