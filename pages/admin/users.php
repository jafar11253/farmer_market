<?php
// pages/admin/users.php
include "../../includes/config.php";
require_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: /FARMER_MARKET/index.php");
    exit;
}

$msg = "";

/* Handle delete (remove) user */
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action']) && $_POST['action'] === 'delete_user') {

    $user_id = (int)$_POST['user_id'];

    // Never delete admins
    $check = $conn->prepare("SELECT role FROM users WHERE id=?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $user = $check->get_result()->fetch_assoc();

    if (!$user) {
        $msg = "User not found.";
    } elseif ($user['role'] === 'admin') {
        $msg = "You cannot delete admin accounts.";
    } else {
        // Delete all related data first (cascade delete)
        // Important: Delete in correct order to respect foreign key constraints
        
        // 1. Get all listings for this user to delete their bids and orders
        $listings = $conn->query("SELECT id FROM listings WHERE user_id=$user_id");
        while ($listing = $listings->fetch_assoc()) {
            $lid = $listing['id'];
            // Delete reviews on their orders
            $conn->query("DELETE FROM reviews WHERE order_id IN (SELECT id FROM orders WHERE listing_id=$lid)");
            // Delete bids on their listings
            $conn->query("DELETE FROM bids WHERE listing_id=$lid");
            // Delete orders on their listings
            $conn->query("DELETE FROM orders WHERE listing_id=$lid");
        }
        
        // 2. Delete reviews they made or received
        $conn->query("DELETE FROM reviews WHERE reviewer_id=$user_id");
        $conn->query("DELETE FROM reviews WHERE reviewed_id=$user_id");
        
        // 3. Delete their listings
        $conn->query("DELETE FROM listings WHERE user_id=$user_id");
        
        // 4. Delete their cart items
        $conn->query("DELETE FROM cart WHERE user_id=$user_id");
        
        // 5. Delete their orders (as buyer)
        $conn->query("DELETE FROM orders WHERE user_id=$user_id");
        
        // 6. Delete their bids
        $conn->query("DELETE FROM bids WHERE user_id=$user_id");
        
        // 7. Finally, delete the user
        $del = $conn->prepare("DELETE FROM users WHERE id=?");
        $del->bind_param("i", $user_id);
        if ($del->execute()) {
            $msg = "User and all related data removed successfully.";
        } else {
            $msg = "Error removing user: " . $del->error;
        }
    }
}

/* Load all farmers & buyers */
$sql = "SELECT id, username, email, role, district, created_at
        FROM users
        WHERE role IN ('farmer','buyer')
        ORDER BY role, username";
$users = $conn->query($sql);

include "../../includes/header.php";
?>

<style>
    .users-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 2rem;
    }

    .users-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        border-bottom: 3px solid var(--primary-color);
        padding-bottom: 1rem;
    }

    .users-header h1 {
        margin: 0;
        font-size: 2.2rem;
        color: var(--dark);
        border: none;
    }

    .user-count {
        background: var(--primary-color);
        color: white;
        padding: 0.6rem 1.2rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-weight: 500;
        border-left: 4px solid;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-color: #28a745;
    }

    .users-table-wrapper {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow-md);
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
    }

    .users-table thead {
        background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
        color: white;
    }

    .users-table th {
        padding: 1.2rem;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid var(--primary-color);
    }

    .users-table td {
        padding: 1.2rem;
        border-bottom: 1px solid #ecf0f1;
    }

    .users-table tbody tr {
        transition: background 0.3s ease;
    }

    .users-table tbody tr:hover {
        background: #f8f9fa;
    }

    .users-table tbody tr:nth-child(even) {
        background: #fafbfc;
    }

    .serial {
        font-weight: 700;
        color: var(--primary-color);
        font-size: 1.05rem;
    }

    .username {
        font-weight: 600;
        color: var(--dark);
    }

    .role-badge {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-align: center;
    }

    .role-farmer {
        background: #fff3cd;
        color: #856404;
    }

    .role-buyer {
        background: #d1ecf1;
        color: #0c5460;
    }

    .email {
        color: var(--gray-dark);
        font-size: 0.95rem;
    }

    .district {
        color: var(--gray);
        font-weight: 500;
    }

    .joined {
        color: var(--gray);
        font-size: 0.9rem;
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
    }

    .remove-btn:hover {
        background: #ff5252;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
    }

    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: var(--gray);
    }

    .empty-state i {
        font-size: 3rem;
        opacity: 0.3;
        margin-bottom: 1rem;
    }

    .back-link {
        display: inline-block;
        margin-top: 2rem;
        padding: 0.8rem 1.6rem;
        background: var(--primary-color);
        color: white;
        text-decoration: none;
        border-radius: 6px;
        transition: all 0.3s;
        font-weight: 600;
    }

    .back-link:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
    }

    @media (max-width: 768px) {
        .users-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .users-table {
            font-size: 0.9rem;
        }

        .users-table th,
        .users-table td {
            padding: 0.8rem;
        }

        .remove-btn {
            padding: 0.5rem 0.8rem;
            font-size: 0.85rem;
        }
    }
</style>

<div class="users-container">
    <div class="users-header">
        <h1><i class="fas fa-users"></i> Manage Users</h1>
        <span class="user-count"><i class="fas fa-user-group"></i> <?php echo $users->num_rows; ?> User<?php echo $users->num_rows !== 1 ? 's' : ''; ?></span>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <?php if ($users->num_rows === 0): ?>
        <div class="empty-state">
            <i class="fas fa-user-slash"></i>
            <h3>No farmers or buyers found</h3>
            <p>Currently, there are no active farmers or buyers in the system.</p>
        </div>
    <?php else: ?>
        <div class="users-table-wrapper">
            <table class="users-table">
                <thead>
                    <tr>
                        <th style="width: 5%;"><i class="fas fa-hashtag"></i> S.No</th>
                        <th style="width: 15%;"><i class="fas fa-user"></i> Username</th>
                        <th style="width: 12%;"><i class="fas fa-id-card"></i> Role</th>
                        <th style="width: 25%;"><i class="fas fa-envelope"></i> Email</th>
                        <th style="width: 15%;"><i class="fas fa-map-marker-alt"></i> District</th>
                        <th style="width: 18%;"><i class="fas fa-calendar"></i> Joined</th>
                        <th style="width: 10%;"><i class="fas fa-cogs"></i> Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $serial = 1;
                    while ($u = $users->fetch_assoc()): 
                    ?>
                        <tr>
                            <td class="serial"><?php echo $serial++; ?></td>
                            <td class="username"><?php echo htmlspecialchars($u['username']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $u['role']; ?>">
                                    <?php echo ucfirst($u['role']); ?>
                                </span>
                            </td>
                            <td class="email"><?php echo htmlspecialchars($u['email']); ?></td>
                            <td class="district"><?php echo htmlspecialchars($u['district']); ?></td>
                            <td class="joined"><?php echo date('M d, Y H:i', strtotime($u['created_at'])); ?></td>
                            <td>
                                <form method="POST"
                                      onsubmit="return confirm('⚠️ Are you sure? This will delete the user and all their data!');"
                                      style="display:inline;">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <button type="submit" class="remove-btn">
                                        <i class="fas fa-trash-alt"></i> Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <a href="/FARMER_MARKET/pages/admin/dashboard.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<?php include "../../includes/footer.php"; ?>
