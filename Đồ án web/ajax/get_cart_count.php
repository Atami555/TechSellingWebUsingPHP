<?php
session_start();

header('Content-Type: application/json');

$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

echo json_encode(['count' => $cart_count]);
?>
