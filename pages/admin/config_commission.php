<?php
// pages/admin/config_commission.php
include "../../includes/config.php";
require_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: /FARMER_MARKET/index.php");
    exit;
}

$msg = "";

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rate = floatval($_POST['commission_rate']);

    if ($rate < 0 || $rate > 100) {
        $msg = "Commission rate must be between 0 and 100.";
    } else {
        // Check if exists
        $res = $conn->query("SELECT id FROM config WHERE key_name='commission_rate' LIMIT 1");
        if ($res && $res->num_rows) {
            $row = $res->fetch_assoc();
            $id = (int)$row['id'];
            $stmt = $conn->prepare("UPDATE config SET value=? WHERE id=?");
            $rate_str = (string)$rate;
            $stmt->bind_param("si", $rate_str, $id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("INSERT INTO config (key_name, value) VALUES ('commission_rate', ?)");
            $rate_str = (string)$rate;
            $stmt->bind_param("s", $rate_str);
            $stmt->execute();
        }
        $msg = "Commission rate updated successfully.";
    }
}

// Get current value
$res = $conn->query("SELECT value FROM config WHERE key_name='commission_rate' LIMIT 1");
$current_rate = $res && $res->num_rows ? $res->fetch_assoc()['value'] : "5";

include "../../includes/header.php";
?>

<h1>Configure Platform Commission</h1>

<?php if ($msg): ?>
    <p><?php echo htmlspecialchars($msg); ?></p>
<?php endif; ?>

<form method="POST" class="admin-form">
    <label>Commission Rate (%)</label>
    <input type="number" name="commission_rate" step="0.01" min="0" max="100"
           value="<?php echo htmlspecialchars($current_rate); ?>" required>
    <button type="submit" class="admin-btn">Save</button>
</form>

<p><a href="/FARMER_MARKET/pages/admin/dashboard.php">Back to Dashboard</a></p>

<?php include "../../includes/footer.php"; ?>
