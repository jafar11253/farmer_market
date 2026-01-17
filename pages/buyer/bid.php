<?php
// pages/buyer/bid.php

include "../../includes/config.php";
require_login();

if ($_SESSION['role'] !== 'buyer') {
    header("Location: /farmer_market/index.php");
    exit;
}

if (empty($_GET['id'])) {
    die("Auction ID required.");
}
$listing_id = (int)$_GET['id'];

// Get auction info with farmer & optional winner info
$sql = "SELECT l.*, u.username AS farmer_name, w.username AS winner_name
        FROM listings l
        JOIN users u ON u.id = l.user_id
        LEFT JOIN users w ON w.id = l.winner_id
        WHERE l.id = ? AND l.type = 'auction'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $listing_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) {
    die("Auction not found.");
}

$farmer_id     = (int)$listing['user_id'];
$buyer_id      = (int)$_SESSION['user_id'];
$now           = date('Y-m-d H:i:s');
$auction_start = $listing['auction_start'];
$auction_end   = $listing['auction_end'];

// bidding allowed only if active and within time window
$can_bid = ($listing['status'] === 'active') &&
           (($auction_start === null) || ($auction_start <= $now)) &&
           (($auction_end   === null) || ($auction_end   >  $now));

// current highest bid
$stmt2 = $conn->prepare(
    "SELECT b.*, us.username AS buyer_name
     FROM bids b
     JOIN users us ON us.id = b.user_id
     WHERE b.listing_id = ?
     ORDER BY b.bid_amount DESC, b.timestamp ASC
     LIMIT 1"
);
$stmt2->bind_param("i", $listing_id);
$stmt2->execute();
$highest = $stmt2->get_result()->fetch_assoc();

// min increment from DB, default 5
$min_increment = isset($listing['min_increment']) && $listing['min_increment'] !== null
    ? (float)$listing['min_increment']
    : 5.00;

// compute next minimum bid
if ($highest) {
    $min_bid = $highest['bid_amount'] + $min_increment;
} else {
    $min_bid = (float)$listing['starting_bid'];
}

$msg = "";

/* -------------------------------
   HANDLE NEW BID (POST)
--------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $can_bid) {
    $new_bid = (float)$_POST['bid_amount'];

    // prevent farmer bidding on own product
    if ($buyer_id === $farmer_id) {
        $msg = "You cannot bid on your own product.";
    } else {
        // re-check highest & min_bid for safety
        $stmt2->execute();
        $highest = $stmt2->get_result()->fetch_assoc();

        if ($highest) {
            $min_bid = $highest['bid_amount'] + $min_increment;
        } else {
            $min_bid = (float)$listing['starting_bid'];
        }

        if ($new_bid < $min_bid) {
            $msg = "Your bid must be at least BDT " . number_format($min_bid, 2);
        } else {
            $stmt3 = $conn->prepare(
                "INSERT INTO bids (listing_id, user_id, bid_amount)
                 VALUES (?, ?, ?)"
            );
            $stmt3->bind_param("iid", $listing_id, $buyer_id, $new_bid);

            if ($stmt3->execute()) {
                // avoid duplicate on refresh
                header("Location: bid.php?id=" . $listing_id);
                exit;
            } else {
                $msg = "Error placing bid: " . $stmt3->error;
            }
        }
    }
}

// refresh highest bid after possible change
$stmt2->execute();
$highest = $stmt2->get_result()->fetch_assoc();

// recent bid history (last 10)
$history_stmt = $conn->prepare(
    "SELECT b.*, u.username
     FROM bids b
     JOIN users u ON u.id = b.user_id
     WHERE b.listing_id = ?
     ORDER BY b.timestamp DESC
     LIMIT 10"
);
$history_stmt->bind_param("i", $listing_id);
$history_stmt->execute();
$history_res = $history_stmt->get_result();

// countdown timer
$timerId    = "timer_auction";
$endTimeISO = $auction_end ? date('Y-m-d\TH:i:s', strtotime($auction_end)) : "";

include "../../includes/header.php";
?>
<h2>Auction: <?php echo htmlspecialchars($listing['title']); ?></h2>

<p>Farmer: <?php echo htmlspecialchars($listing['farmer_name']); ?></p>
<p>Lot Quantity:
    <span id="auction_quantity">
        <?php echo (int)$listing['quantity']; ?>
    </span>
</p>

<p>Starting Bid: BDT <?php echo number_format($listing['starting_bid'], 2); ?></p>
<p>Auction Start: <?php echo htmlspecialchars($listing['auction_start']); ?></p>
<p>Auction End: <?php echo htmlspecialchars($listing['auction_end']); ?></p>

<?php if ($endTimeISO): ?>
    <p>Time left: <span id="<?php echo $timerId; ?>"></span></p>
    <script>
        // assumes you already have startAuctionTimer in assets/js/script.js
        startAuctionTimer("<?php echo $endTimeISO; ?>", "<?php echo $timerId; ?>");
    </script>
<?php endif; ?>

<p id="highest_bid_line">
    <?php if ($highest): ?>
        Current Highest Bid:
        <span id="highest_bid_amount">
            <?php echo number_format($highest['bid_amount'], 2); ?>
        </span> BDT
        by <span id="highest_bidder">
            <?php echo htmlspecialchars($highest['buyer_name']); ?>
        </span>
    <?php else: ?>
        No bids yet.
    <?php endif; ?>
</p>

<?php if ($msg): ?>
    <p><?php echo htmlspecialchars($msg); ?></p>
<?php endif; ?>

<?php if ($can_bid): ?>
    <?php
    if ($highest) {
        $min_bid = $highest['bid_amount'] + $min_increment;
    } else {
        $min_bid = (float)$listing['starting_bid'];
    }
    ?>
    <p>Next minimum bid:
        <strong>BDT <?php echo number_format($min_bid, 2); ?></strong>
    </p>
    <form method="POST">
        <label>Your Bid (BDT)</label>
        <input type="number"
               step="0.01"
               name="bid_amount"
               min="<?php echo htmlspecialchars($min_bid); ?>"
               required>
        <button type="submit">Place Bid</button>
    </form>
<?php else: ?>
    <p><strong>Bidding is closed for this auction.</strong></p>

    <?php if ($listing['status'] === 'ended' || ($auction_end && $auction_end <= $now)): ?>
        <?php if ($listing['winner_id']): ?>
            <p>Winner: <?php echo htmlspecialchars($listing['winner_name']); ?></p>
            <?php if ((int)$listing['winner_id'] === $buyer_id): ?>
                <p><strong>You won this auction! Please go to your Orders page to complete payment.</strong></p>
            <?php endif; ?>
        <?php else: ?>
            <p>No bids were placed for this auction.</p>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

<hr>

<h3>Recent Bids</h3>
<?php if ($history_res->num_rows === 0): ?>
    <p>No bids yet.</p>
<?php else: ?>
    <table class="table">
        <tr>
            <th>Bidder</th>
            <th>Amount (BDT)</th>
            <th>Time</th>
        </tr>
        <?php while ($b = $history_res->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($b['username']); ?></td>
                <td><?php echo number_format($b['bid_amount'], 2); ?></td>
                <td><?php echo $b['timestamp']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php endif; ?>

<!-- Live update of auction quantity and highest bid -->
<script>
    const listingId = <?php echo (int)$listing_id; ?>;

    function refreshAuctionStatus() {
        fetch('auction_status.php?id=' + listingId)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.log(data.error);
                    return;
                }

                // update quantity
                if (typeof data.quantity !== 'undefined') {
                    const qtyEl = document.getElementById('auction_quantity');
                    if (qtyEl) qtyEl.textContent = data.quantity;
                }

                const highestLine = document.getElementById('highest_bid_line');

                if (data.highest_bid !== null && data.highest_bid !== undefined) {
                    const amtEl = document.getElementById('highest_bid_amount');
                    const bidderEl = document.getElementById('highest_bidder');

                    if (amtEl && bidderEl) {
                        amtEl.textContent = parseFloat(data.highest_bid).toFixed(2);
                        bidderEl.textContent = data.highest_bidder || 'Unknown';
                    } else if (highestLine) {
                        highestLine.innerHTML =
                            'Current Highest Bid: <span id="highest_bid_amount">' +
                            parseFloat(data.highest_bid).toFixed(2) +
                            '</span> BDT by <span id="highest_bidder">' +
                            (data.highest_bidder || 'Unknown') +
                            '</span>';
                    }
                } else {
                    if (highestLine) {
                        highestLine.textContent = 'No bids yet.';
                    }
                }

                // optionally, if auction status changed to ended, you could refresh page or show a message
                // e.g., if (data.status === 'ended') { location.reload(); }
            })
            .catch(err => console.error(err));
    }

    // poll every 5 seconds
    setInterval(refreshAuctionStatus, 5000);
</script>

<?php include "../../includes/footer.php"; ?>
