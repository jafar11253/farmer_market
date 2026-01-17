<?php
include "../../includes/config.php";
require_login();
if ($_SESSION['role'] !== 'farmer') {
    header("Location: /farmer_market/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get farmer district
$farmer   = $conn->query("SELECT district FROM users WHERE id = $user_id")->fetch_assoc();
$district = $farmer ? $farmer['district'] : null;

// Get categories
$cats = $conn->query("SELECT * FROM categories");

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title']);
    $category_id  = (int)$_POST['category_id'];
    $price        = ($_POST['price'] !== "") ? $_POST['price'] : null;
    $quantity     = (int)$_POST['quantity'];
    $description  = trim($_POST['description']);
    $type         = $_POST['type'];
    $starting_bid = ($_POST['starting_bid'] !== "") ? $_POST['starting_bid'] : null;
    $auction_start = ($_POST['auction_start'] !== "") ? $_POST['auction_start'] : null;
    $auction_end   = ($_POST['auction_end']   !== "") ? $_POST['auction_end']   : null;

    // IMAGE UPLOAD (required)
    $images = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDirFs  = "../../assets/img/uploads/";
        $uploadDirWeb = "assets/img/uploads/";

        $tmpName  = $_FILES['image']['tmp_name'];
        $origName = basename($_FILES['image']['name']);
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowed  = ['jpg','jpeg','png','gif','webp'];

        if (!in_array($ext, $allowed)) {
            $msg = "Invalid image type. Allowed: jpg, jpeg, png, gif, webp.";
        } else {
            if (!is_dir($uploadDirFs)) {
                mkdir($uploadDirFs, 0777, true);
            }
            $newName    = time() . "_" . mt_rand(1000, 9999) . "." . $ext;
            $targetPath = $uploadDirFs . $newName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $images = json_encode([$uploadDirWeb . $newName]);
            } else {
                $msg = "Failed to upload image.";
            }
        }
    } else {
        $msg = "Product image is required.";
    }

    if ($msg === "") {
        $price_str        = $price        !== null ? (string)$price        : null;
        $starting_bid_str = $starting_bid !== null ? (string)$starting_bid : null;
        $auction_start_str= $auction_start!== null ? (string)$auction_start: null;
        $auction_end_str  = $auction_end  !== null ? (string)$auction_end  : null;
        $district_str     = $district     !== null ? (string)$district     : null;

        $stmt = $conn->prepare(
            "INSERT INTO listings
             (user_id, category_id, title, description, price, quantity, images, type, starting_bid, auction_start, auction_end, district)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        // types: i i s s s i s s s s s s  -> "iisssissssss"
        $stmt->bind_param(
            "iisssissssss",
            $user_id,
            $category_id,
            $title,
            $description,
            $price_str,
            $quantity,
            $images,
            $type,
            $starting_bid_str,
            $auction_start_str,
            $auction_end_str,
            $district_str
        );

        if ($stmt->execute()) {
            $msg = "Listing created successfully.";
        } else {
            $msg = "Error creating listing: " . $stmt->error;
        }
    }
}

include "../../includes/header.php";
?>
<h2>Create Listing</h2>
<?php if ($msg): ?>
    <p><?php echo htmlspecialchars($msg); ?></p>
<?php endif; ?>

<p>Your district:
    <strong><?php echo $district ? htmlspecialchars($district) : "Not set (update your profile)"; ?></strong>
</p>

<form method="POST" enctype="multipart/form-data">
    <label>Title</label>
    <input type="text" name="title" required>

    <label>Category</label>
    <select name="category_id" required>
        <?php while ($c = $cats->fetch_assoc()): ?>
            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
        <?php endwhile; ?>
    </select>

    <label>Description</label>
    <textarea name="description" required></textarea>

    <label>Type</label>
    <select name="type" required>
        <option value="fixed">Fixed Price</option>
        <option value="auction">Auction</option>
    </select>

    <label>Price (for fixed products)</label>
    <input type="number" step="0.01" name="price">

    <label>Quantity</label>
    <input type="number" name="quantity" required>

    <label>Starting Bid (for auction)</label>
    <input type="number" step="0.01" name="starting_bid">

    <label>Auction Start (YYYY-MM-DD HH:MM:SS)</label>
    <input type="text" name="auction_start">

    <label>Auction End (YYYY-MM-DD HH:MM:SS)</label>
    <input type="text" name="auction_end">

    <label>Product Image (required)</label>
    <input type="file" name="image" accept="image/*" required>

    <button type="submit">Create Listing</button>
</form>

<?php include "../../includes/footer.php"; ?>
