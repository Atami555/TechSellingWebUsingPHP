<?php
session_start();
require_once '../includes/functions.php';

header('Content-Type: application/json');

$cart_total = getCartTotal();

echo json_encode(['total' => $cart_total]);
?>
