<?php
include "../../includes/config.php";
require_login();

if ($_SESSION['role'] !== 'buyer') {
    header("Location: /farmer_market/index.php");
    exit;
}

$districts = ["Dhaka","Chattogram","Rajshahi","Khulna","Sylhet","Barishal","Rangpur","Mymensingh"];

// Build query with optional district filter
$sql = "SELECT l.*, u.district AS farmer_district, c.name AS category_name
        FROM listings l
        JOIN users u ON u.id = l.user_id
        JOIN categories c ON c.id = l.category_id
        WHERE l.status = 'active'";

$params = [];
$types  = "";

if (!empty($_GET['district'])) {
    $sql    .= " AND l.district = ?";
    $params[] = $_GET['district'];
    $types  .= "s";
}

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

include "../../includes/header.php";
?>

<style>
.browse-header {
    text-align: center;
    margin-bottom: 2rem;
}

.browse-header h2 {
    font-size: 2.5rem;
    color: var(--dark);
    margin-bottom: 0.5rem;
    border-bottom: 4px solid var(--primary-color);
    display: inline-block;
    padding-bottom: 0.5rem;
}

.filter-section {
    background: var(--white);
    padding: 1.5rem;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    margin-bottom: 2.5rem;
}

.filter-section form {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
    flex-wrap: wrap;
    margin: 0;
}

.filter-section .form-group {
    flex: 1;
    min-width: 250px;
}

.filter-section label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--dark);
}

.filter-section select {
    width: 100%;
    margin-bottom: 0;
}

.filter-section button {
    margin-bottom: 0;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    margin: 2rem 0;
}

.product {
    background: var(--white);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: var(--transition);
    border: 2px solid #f0f0f0;
    display: flex;
    flex-direction: column;
}

.product:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-color);
}

.product img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    margin: 0;
    display: block;
}

.product-content {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product h3 {
    font-size: 1.4rem;
    color: var(--dark);
    margin-bottom: 1rem;
    font-weight: 600;
}

.product p {
    color: var(--gray-dark);
    margin-bottom: 0.6rem;
    font-size: 0.95rem;
}

.product p:last-of-type {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-top: auto;
    padding-top: 0.5rem;
}

.product a {
    display: block;
    text-align: center;
    background: var(--primary-color);
    color: var(--white);
    padding: 0.85rem 1.5rem;
    border-radius: var(--radius-sm);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    margin-top: 1rem;
}

.product a:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
}

.no-products {
    text-align: center;
    padding: 3rem;
    background: var(--gray-light);
    border-radius: var(--radius-md);
    color: var(--gray);
    font-size: 1.2rem;
}

@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .filter-section form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-section .form-group {
        width: 100%;
        min-width: auto;
    }
    
    .browse-header h2 {
        font-size: 2rem;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div class="browse-header">
    <h2>Browse Products</h2>
</div>

<div class="filter-section">
    <form method="GET">
        <div class="form-group">
            <label>Filter by District</label>
            <select name="district">
                <option value="">All Districts</option>
                <?php foreach ($districts as $d): ?>
                    <option value="<?php echo $d; ?>"
                        <?php if (!empty($_GET['district']) && $_GET['district'] === $d) echo 'selected'; ?>>
                        <?php echo $d; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit">Apply</button>
    </form>
</div>

<div class="products-grid">
<?php 
$hasProducts = false;
while ($row = $result->fetch_assoc()): 
    $hasProducts = true;
    // Decode images JSON
    $imgPath = null;
    if (!empty($row['images'])) {
        $decoded = json_decode($row['images'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded)) {
            $imgPath = $decoded[0];
        } else {
            // If not valid JSON, fall back to raw string
            $imgPath = $row['images'];
        }
    }
    ?>
    <div class="product">
        <?php if ($imgPath): ?>
            <img src="/FARMER_MARKET/<?php echo htmlspecialchars($imgPath); ?>"
                 alt="<?php echo htmlspecialchars($row['title']); ?>">
        <?php endif; ?>

        <div class="product-content">
            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
            <p>📦 Category: <?php echo htmlspecialchars($row['category_name']); ?></p>
            <p>📍 Farmer District: <?php echo htmlspecialchars($row['district']); ?></p>

            <?php if ($row['type'] === 'fixed'): ?>
                <p>💰 Price: BDT <?php echo number_format($row['price'], 2); ?></p>
            <?php else: ?>
                <p>🔨 Auction Starting Bid: BDT <?php echo number_format($row['starting_bid'], 2); ?></p>
            <?php endif; ?>

            <a href="product.php?id=<?php echo $row['id']; ?>">View Details</a>
        </div>
    </div>
<?php endwhile; ?>
</div>

<?php if (!$hasProducts): ?>
    <div class="no-products">
        <p>No products found. Try changing the filter.</p>
    </div>
<?php endif; ?>

<?php include "../../includes/footer.php"; ?>
