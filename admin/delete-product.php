
<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit;
}

include "../includes/ecommerce_db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["product_id"])) {

    $id = (int) $_POST["product_id"];

    $stmt = $conn->prepare(
        "UPDATE products SET status = 'Unavailable' WHERE product_id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt->close();
}

header("Location: products.php");
exit;
?>

