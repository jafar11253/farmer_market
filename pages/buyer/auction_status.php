<?php
// pages/buyer/auction_status.php
// Returns JSON with current auction state for live updates on bid.php

include "../../includes/config.php";
require_login();

// You can restrict to buyers only if you want:
if ($_SESSION['role'] !== 'buyer') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Forbidden"]);
    exit;
}

header('Content-Type: application/json');

if (empty($_GET['id'])) {
    echo json_encode(["error" => "Missing id"]);
    exit;
}

$listing_id = (int)$_GET['id'];

// Get basic auction info
$sql = "SELECT id, title, quantity, status, auction_end, starting_bid, min_increment
        FROM listings
        WHERE id = ? AND type = 'auction'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $listing_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) {
    echo json_encode(["error" => "Auction not found"]);
    exit;
}

// Get highest bid
$stmt2 = $conn->prepare(
    "SELECT b.bid_amount, u.username
     FROM bids b
     JOIN users u ON u.id = b.user_id
     WHERE b.listing_id = ?
     ORDER BY b.bid_amount DESC, b.timestamp ASC
     LIMIT 1"
);
$stmt2->bind_param("i", $listing_id);
$stmt2->execute();
$highest = $stmt2->get_result()->fetch_assoc();

$response = [
    "id"             => (int)$listing['id'],
    "title"          => $listing['title'],
    "quantity"       => (int)$listing['quantity'],
    "status"         => $listing['status'],
    "auction_end"    => $listing['auction_end'],
    "starting_bid"   => $listing['starting_bid'] !== null ? (float)$listing['starting_bid'] : null,
    "min_increment"  => $listing['min_increment'] !== null ? (float)$listing['min_increment'] : null,
    "highest_bid"    => $highest ? (float)$highest['bid_amount'] : null,
    "highest_bidder" => $highest ? $highest['username'] : null
];

echo json_encode($response);
