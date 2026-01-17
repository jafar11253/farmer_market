<?php
// pages/farmer/manage_listings.php

include "../../includes/config.php";
require_login();

// Only farmers can access this page
if ($_SESSION['role'] !== 'farmer') {
    header("Location: /farmer_market/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = "";

// 1) Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'delete_listing') {

    $listing_id = (int)$_POST['listing_id'];

    // Make sure this listing belongs to this farmer
    $check_stmt = $conn->prepare("SELECT id FROM listings WHERE id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $listing_id, $user_id);
    $check_stmt->execute();
    $check_res = $check_stmt->get_result();

    if ($check_res->num_rows === 0) {
        // Listing does not belong to this farmer
        $msg = "You are not allowed to delete this product.";
    } else {
        // Delete related records first (to handle foreign key constraints)
        
        // Delete bids related to this listing
        $conn->query("DELETE FROM bids WHERE listing_id = $listing_id");
        
        // Delete orders related to this listing
        $conn->query("DELETE FROM orders WHERE listing_id = $listing_id");
        
        // Now delete the listing itself
        $del_stmt = $conn->prepare("DELETE FROM listings WHERE id = ? AND user_id = ?");
        $del_stmt->bind_param("ii", $listing_id, $user_id);

        if ($del_stmt->execute()) {
            $msg = "Product deleted successfully.";
        } else {
            $msg = "Error deleting product: " . $del_stmt->error;
        }
    }
}

// 2) Fetch all listings belonging to this farmer
$listings_sql = "SELECT l.*, c.name AS category_name
                 FROM listings l
                 JOIN categories c ON c.id = l.category_id
                 WHERE l.user_id = $user_id
                 ORDER BY l.id DESC";
$listings_res = $conn->query($listings_sql);

include "../../includes/header.php";
?>

<style>
    .listings-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .listings-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 2rem;
        margin-bottom: 3.5rem;
        background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
        color: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(39, 174, 96, 0.15);
    }

    .listings-header h2 {
        margin: 0;
        font-size: 2.2rem;
        color: white;
        border: none;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .add-product-link {
        display: inline-block;
        background: white;
        color: var(--primary-color);
        padding: 0.85rem 2rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 700;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
        border: 2px solid white;
        white-space: nowrap;
    }

    .add-product-link i {
        font-size: 1.3rem;
    }

    .add-product-link:hover {
        background: var(--primary-color);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(39, 174, 96, 0.3);
    }

    .add-product-link i {
        font-size: 1.3rem;
    }

    .table-wrapper {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow-md);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
        color: white;
        padding: 1.2rem;
        text-align: left;
        font-weight: 700;
        font-size: 1rem;
        border-bottom: 3px solid var(--primary-color);
    }

    .table td {
        padding: 1.2rem;
        border-bottom: 1px solid #ecf0f1;
        font-size: 0.95rem;
    }

    .table tbody tr {
        transition: background 0.3s ease;
    }

    .table tbody tr:hover {
        background: #f8f9fa;
    }

    .table tbody tr:nth-child(even) {
        background: #fafbfc;
    }

    .product-id {
        font-weight: 700;
        color: var(--primary-color);
        font-size: 1.1rem;
        background: #e8f5e9;
        padding: 0.4rem 0.8rem;
        border-radius: 4px;
        text-align: center;
    }

    .product-title {
        font-weight: 600;
        color: var(--dark);
    }

    .product-type {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        background: #e3f2fd;
        color: #1976d2;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .status-active {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        background: #d4edda;
        color: #155724;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .status-ended {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        background: #f8d7da;
        color: #721c24;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .delete-btn {
        background: #ff6b6b;
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 600;
    }

    .delete-btn:hover {
        background: #ff5252;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
    }

    .empty-state {
        text-align: center;
        padding: 2rem 2rem;
        background: linear-gradient(135deg, #f8f9fa, #e8f5e9);
        border-radius: 16px;
        color: var(--gray);
        border: 2px dashed var(--primary-light);
    }

    .empty-state i {
        font-size: 5rem;
        color: var(--primary-light);
        margin-bottom: 2rem;
        display: block;
    }

    .empty-state h3 {
        font-size: 2rem;
        color: var(--dark);
        margin: 1rem 0;
        border: none;
    }

    .empty-state p {
        font-size: 1.1rem;
        color: var(--gray-dark);
        margin: 1.2rem 0;
    }

    .empty-state .add-product-link {
        margin-top: 2rem;
        padding: 1rem 2.5rem;
        background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
        color: white;
        border: none;
        font-size: 1.05rem;
    }

    .empty-state .add-product-link:hover {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: white;
    }

</style>

<?php if ($msg): ?>
    <p><?php echo htmlspecialchars($msg); ?></p>
<?php endif; ?>

<div class="listings-container">
    <div class="listings-header">
        <h2><i class="fas fa-boxes"></i> Manage Your Products</h2>
        <a href="create_listing.php" class="add-product-link"><i class="fas fa-plus"></i> Add New Product</a>
    </div>

    <?php if ($listings_res->num_rows === 0): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No products yet</h3>
            <p>You have not added any products yet. Start by creating your first listing!</p>
            <a href="create_listing.php" class="add-product-link" style="margin-top: 1rem;">
                <i class="fas fa-plus"></i> Create Your First Product
            </a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> S.No</th>
                        <th><i class="fas fa-tag"></i> Title</th>
                        <th><i class="fas fa-list"></i> Category</th>
                        <th><i class="fas fa-cube"></i> Type</th>
                        <th><i class="fas fa-money-bill"></i> Price / Auction Info</th>
                        <th><i class="fas fa-info-circle"></i> Status</th>
                        <th><i class="fas fa-cogs"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $serial = 1;
                    while ($row = $listings_res->fetch_assoc()): 
                    ?>
                        <tr>
                            <td class="product-id"><?php echo $serial++; ?></td>
                            <td class="product-title"><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td>
                                <span class="product-type">
                                    <?php echo ucfirst($row['type']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['type'] === 'fixed'): ?>
                                    <strong>BDT <?php echo number_format($row['price'], 2); ?></strong><br>
                                    <small>Qty: <?php echo (int)$row['quantity']; ?> units</small>
                                <?php else: ?>
                                    <strong>BDT <?php echo number_format($row['starting_bid'], 2); ?></strong> (Starting)<br>
                                    <small>Start: <?php echo date('M d, H:i', strtotime($row['auction_start'])); ?></small><br>
                                    <small>End: <?php echo date('M d, H:i', strtotime($row['auction_end'])); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-<?php echo $row['status']; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                                <?php if ($row['status'] === 'ended' && !empty($row['winner_id'])): ?>
                                    <br><small>Won by ID: <?php echo (int)$row['winner_id']; ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST"
                                      style="display:inline;"
                                      onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    <input type="hidden" name="action" value="delete_listing">
                                    <input type="hidden" name="listing_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="delete-btn">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

<?php include "../../includes/footer.php"; ?>
