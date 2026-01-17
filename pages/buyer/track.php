<?php
include '../../includes/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') header('Location: ../../login.php');
include '../../includes/header.php';

$id = $_GET['id'];
$row = $conn->query("SELECT status FROM orders WHERE id = $id AND user_id = {$_SESSION['user_id']}")->fetch_assoc();
?>

<h2>Track Order #<?php echo $id; ?></h2>
<p>Status: <?php echo $row['status'] ?? 'Not found'; ?></p>
<!-- Farmer updates status in their dashboard -->

<?php include '../../includes/footer.php'; ?>