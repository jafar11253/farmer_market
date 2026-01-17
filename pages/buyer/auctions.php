<?php
include "../../includes/config.php";
require_login();
if ($_SESSION['role'] !== 'buyer') {
    header("Location: /farmer_market/index.php");
    exit;
}

// Helper: close expired auctions and create orders for winners
function closeExpiredAuctions(mysqli $conn) {
    // Get commission rate (default 5% if not set)
    $rate_res = $conn->query("SELECT value FROM config WHERE key_name='commission_rate' LIMIT 1");
    $rate_row = $rate_res ? $rate_res->fetch_assoc() : null;
    $commission_rate = $rate_row ? (float)$rate_row['value'] : 5.0;

    $now = date('Y-m-d H:i:s');

    // Find all active auctions that have ended
    $sql = "SELECT id FROM listings
            WHERE type='auction' AND status='active' AND auction_end IS NOT NULL AND auction_end <= ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $now);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($listing = $res->fetch_assoc()) {
        $listing_id = (int)$listing['id'];

        // Get highest bid for this listing
        $stmtBid = $conn->prepare(
            "SELECT * FROM bids 
             WHERE listing_id=? 
             ORDER BY bid_amount DESC, timestamp ASC 
             LIMIT 1"
        );
        $stmtBid->bind_param("i", $listing_id);
        $stmtBid->execute();
        $bid_res = $stmtBid->get_result();

        if ($winner = $bid_res->fetch_assoc()) {
            $buyer_id   = (int)$winner['user_id'];
            $bid_amount = (float)$winner['bid_amount'];
            $qty        = 1; // auction "lot" quantity

            $commission = round($bid_amount * $commission_rate / 100, 2);

            // Create order for winner (payment pending)
            $stmtOrder = $conn->prepare(
                "INSERT INTO orders 
                 (listing_id, user_id, quantity, total_price, status, commission_deducted, payment_status)
                 VALUES (?, ?, ?, ?, 'pending', ?, 'pending')"
            );
            $stmtOrder->bind_param("iiidd", $listing_id, $buyer_id, $qty, $bid_amount, $commission);
            $stmtOrder->execute();

            // Mark auction as ended and save winner_id
            $stmtEnd = $conn->prepare("UPDATE listings SET status='ended', winner_id=? WHERE id=?");
            $stmtEnd->bind_param("ii", $buyer_id, $listing_id);
            $stmtEnd->execute();
        } else {
            // No bids: just mark ended, winner_id stays NULL
            $conn->query("UPDATE listings SET status='ended' WHERE id=$listing_id");
        }
    }
}

// 1) Close auctions that have passed end time
closeExpiredAuctions($conn);

// 2) Show only active & started auctions
$now = date('Y-m-d H:i:s');

$sql = "SELECT l.*, u.username AS farmer_name
        FROM listings l
        JOIN users u ON u.id = l.user_id
        WHERE l.type='auction'
          AND l.status='active'
          AND (l.auction_start IS NULL OR l.auction_start <= ?)
        ORDER BY l.auction_end ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $now);
$stmt->execute();
$res = $stmt->get_result();

include "../../includes/header.php";
?>
<h2>Active Auctions</h2>

<?php if ($res->num_rows === 0): ?>
    <p>No active auctions at the moment.</p>
<?php else: ?>
    <?php while ($row = $res->fetch_assoc()): ?>
        <?php
        // Find current highest bid
        $stmtHighest = $conn->prepare(
            "SELECT b.*, us.username AS buyer_name
             FROM bids b
             JOIN users us ON us.id = b.user_id
             WHERE b.listing_id=?
             ORDER BY b.bid_amount DESC, b.timestamp ASC
             LIMIT 1"
        );
        $listing_id = (int)$row['id'];
        $stmtHighest->bind_param("i", $listing_id);
        $stmtHighest->execute();
        $highest = $stmtHighest->get_result()->fetch_assoc();

        // Prepare timer ID and time string
        $timerId = "timer_" . $listing_id;
        $endTimeISO = $row['auction_end'] ? date('Y-m-d\TH:i:s', strtotime($row['auction_end'])) : "";
        ?>
        <div class="product">
            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
            <p>Farmer: <?php echo htmlspecialchars($row['farmer_name']); ?></p>
            <p>Starting Bid: BDT <?php echo number_format($row['starting_bid'], 2); ?></p>
            <p>Auction Start: <?php echo htmlspecialchars($row['auction_start']); ?></p>
            <p>Auction End: <?php echo htmlspecialchars($row['auction_end']); ?></p>

            <?php if ($highest): ?>
                <p>Current Highest Bid: BDT <?php echo number_format($highest['bid_amount'], 2); ?>
                    by <?php echo htmlspecialchars($highest['buyer_name']); ?></p>
            <?php else: ?>
                <p>No bids yet.</p>
            <?php endif; ?>

            <?php if ($endTimeISO): ?>
                <p>Time left: <span id="<?php echo $timerId; ?>"></span></p>
                <script>
                    startAuctionTimer("<?php echo $endTimeISO; ?>", "<?php echo $timerId; ?>");
                </script>
            <?php endif; ?>

            <p>
                <a href="bid.php?id=<?php echo $row['id']; ?>">View / Place Bid</a>
            </p>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

<?php include "../../includes/footer.php"; ?>
