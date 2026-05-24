<?php
/**
 * i.php — Short, secure invoice share-link resolver.
 *
 * Usage:  /salonapp/i.php?t=<12-char-token>
 * Token is stored in hr_invoice.share_token (random, non-guessable).
 */
include_once "config.php";
include_once "function.php";

$token = trim($_GET['t'] ?? '');

// Basic sanity check — tokens are 12 hex chars
if (empty($token) || !preg_match('/^[0-9a-f]{12}$/', $token)) {
    http_response_code(404);
    die('<h2>Invalid link</h2>');
}

$safe_token = mysqli_real_escape_string($conn, $token);
$row = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT invoice_id FROM hr_invoice WHERE share_token = '$safe_token' LIMIT 1")
);

if (!$row) {
    http_response_code(404);
    die('<h2>Invoice not found or link has expired.</h2>');
}

// Forward to the existing view page (numeric ID is internal — external URL stays short)
header("Location: print_invoice.php?view=1&invoice_id=" . intval($row['invoice_id']));
exit;
?>
