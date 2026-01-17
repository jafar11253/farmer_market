<?php
include '../../includes/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer') header('Location: ../../login.php');
include '../../includes/header.php';

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if review exists
$exists = $conn->query("SELECT id FROM reviews WHERE order_id = $order_id AND reviewer_id = $user_id");
if ($exists->num_rows > 0) {
    echo "<p>Review already submitted.</p>";
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $reviewed_id = $conn->query("SELECT user_id FROM orders WHERE id = $order_id")->fetch_assoc()['user_id'];
    
    $stmt = $conn->prepare("INSERT INTO reviews (order_id, reviewer_id, reviewed_id, rating, comment, type) VALUES (?, ?, ?, ?, ?, 'farmer_to_buyer')");
    $stmt->bind_param("iiiis", $order_id, $user_id, $reviewed_id, $rating, $comment);
    $stmt->execute();
    $stmt->close();
    echo "<p>Review submitted!</p>";
}
?>

<h2>Review Buyer for Order #<?php echo $order_id; ?></h2>
<form method="POST">
    <select name="rating" required>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
    </select>
    <textarea name="comment" placeholder="Comment"></textarea>
    <button type="submit">Submit</button>
</form>

<?php include '../../includes/footer.php'; ?>