
<?php
session_start();
include "includes/ecommerce_db.php";

$product_id = intval($_POST["product_id"] ?? 0);
$quantity = intval($_POST["quantity"] ?? 1);

if ($product_id <= 0 || $quantity <= 0) {
    header("Location: index.php");
    exit;
}

// Check product and available stock
$stmt = $conn->prepare(
    "SELECT stock FROM products
     WHERE product_id = ? AND status = 'Available'"
);

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product || $product["stock"] <= 0) {
    header("Location: index.php");
    exit;
}

// Create cart if it doesn't exist
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

// Check total quantity against stock
$currentQuantity = $_SESSION["cart"][$product_id] ?? 0;
$newQuantity = $currentQuantity + $quantity;

if ($newQuantity > $product["stock"]) {
    $_SESSION["cart_message"] = "You cannot add more than the available stock.";
} else {
    $_SESSION["cart"][$product_id] = $newQuantity;
    $_SESSION["cart_message"] = "Product added to your cart!";
}

header("Location: cart.php");
exit;
?>
