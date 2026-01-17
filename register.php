<?php
include "includes/config.php";

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $role     = $_POST['role'];
    $district = $_POST['district'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username,email,password,role,district) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssss", $username, $email, $password, $role, $district);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit;
    } else {
        $error = "Error: " . $stmt->error;
    }
}
$districts = ["Dhaka","Chattogram","Rajshahi","Khulna","Sylhet","Barishal","Rangpur","Mymensingh"];
?>

<?php include "includes/header.php"; ?>
<h2>Register</h2>
<?php if ($error): ?><p style="color:red;"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>

<form method="POST">
    <label>Username</label>
    <input type="text" name="username" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <label>Role</label>
    <select name="role" required>
        <option value="">Select Role</option>
        <option value="buyer">Buyer</option>
        <option value="farmer">Farmer</option>
    </select>

    <label>District</label>
    <select name="district" required>
        <option value="">Select District</option>
        <?php foreach ($districts as $d): ?>
            <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Register</button>
</form>

<?php include "includes/footer.php"; ?>
