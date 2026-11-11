
<?php
session_start();
include "includes/ecommerce_db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php?next=checkout");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: checkout.php");
    exit;
}

// Demo credentials only
$correct_mobile = "9800000000";
$correct_mpin = "1234";

$mobile = trim($_POST["mobile"] ?? "");
$mpin = trim($_POST["mpin"] ?? "");

// Verify demo payment credentials
if ($mobile !== $correct_mobile || $mpin !== $correct_mpin) {
    $_SESSION["payment_error"] = "Invalid demo mobile number or MPIN. Please try again.";
    header("Location: payment.php");
    exit;
}

// Get delivery details from session
$user_id = $_SESSION["user_id"];
$address = $_SESSION["delivery_address"] ?? "";
$phone = $_SESSION["delivery_phone"] ?? "";

if ($address == "" || $phone == "" || empty($_SESSION["cart"])) {
    header("Location: checkout.php");
    exit;
}

try {
    $conn->begin_transaction();

    $items = [];
    $total = 0;

    // Verify products and stock
    foreach ($_SESSION["cart"] as $id => $quantity) {

        $id = intval($id);
        $quantity = intval($quantity);

        if ($id <= 0 || $quantity <= 0) {
            throw new Exception("Invalid cart quantity.");
        }

        $stmt = $conn->prepare(
            "SELECT product_id, name, price, stock, status
             FROM products
             WHERE product_id = ?
             FOR UPDATE"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $product = $stmt->get_result()->fetch_assoc();

        if (
            !$product ||
            $product["status"] != "Available" ||
            $product["stock"] < $quantity
        ) {
            throw new Exception("Insufficient stock for " .
                ($product["name"] ?? "a product") .
                ". Please update your cart.");
        }

        $product["quantity"] = $quantity;
        $total += $product["price"] * $quantity;

        $items[] = $product;
    }

    // Create order
    $stmt = $conn->prepare(
        "INSERT INTO orders
        (user_id, total_amount, delivery_address, phone,
         payment_method, order_status)
        VALUES (?, ?, ?, ?, 'eSewa Demo', 'Processing')"
    );

    $stmt->bind_param("idss", $user_id, $total, $address, $phone);
    $stmt->execute();

    $order_id = $conn->insert_id;

    // Save ordered products and reduce stock
    foreach ($items as $item) {

        $stmt = $conn->prepare(
            "INSERT INTO order_items
            (order_id, product_id, product_name, quantity, price)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "iisid",
            $order_id,
            $item["product_id"],
            $item["name"],
            $item["quantity"],
            $item["price"]
        );

        $stmt->execute();

        $stmt = $conn->prepare(
            "UPDATE products
             SET stock = stock - ?
             WHERE product_id = ?"
        );

        $stmt->bind_param(
            "ii",
            $item["quantity"],
            $item["product_id"]
        );

        $stmt->execute();
    }

    // Generate a fake transaction code
    $transaction_code = "DEMO-" . strtoupper(bin2hex(random_bytes(5)));

    // Save simulated payment
    $stmt = $conn->prepare(
        "INSERT INTO payments
        (order_id, transaction_code, amount,
         payment_method, payment_status)
        VALUES (?, ?, ?, 'eSewa Demo', 'Successful')"
    );

    $stmt->bind_param("isd", $order_id, $transaction_code, $total);
    $stmt->execute();

    // Save database changes
    $conn->commit();

    // Clear cart and delivery details
    $_SESSION["cart"] = [];
    unset(
        $_SESSION["delivery_address"],
        $_SESSION["delivery_phone"],
        $_SESSION["payment_error"]
    );

} catch (Throwable $e) {

    $conn->rollback();

    $_SESSION["checkout_error"] = $e->getMessage();
    header("Location: checkout.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Successful - PawHub</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            background: #f5f5f5;
            font-family: Arial, sans-serif;
        }

        .success-container {
            max-width: 600px;
            margin: 65px auto;
            padding: 35px;
            background: white;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.07);
        }

        .success-icon {
            font-size: 65px;
        }

        .success-container h1 {
            color: #55a630;
        }

        .transaction {
            background: #f0f7ec;
            padding: 18px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>

<body>

<div class="success-container">

    <div class="success-icon">✅</div>

    <h1>Payment Successful!</h1>

    <p>Your PawHub order has been placed successfully.</p>

    <div class="transaction">

        <p><strong>Order ID:</strong> #<?php echo $order_id; ?></p>

        <p><strong>Transaction Code:</strong></p>

        <strong><?php echo htmlspecialchars($transaction_code); ?></strong>

        <p><strong>Payment Method:</strong> eSewa Demo</p>

        <p><strong>Payment Status:</strong> Successful (Demo)</p>

        <p>
            <strong>Amount:</strong>
            NPR <?php echo number_format($total, 2); ?>
        </p>

    </div>

    <a href="orders.php" class="btn btn-primary">
        View My Orders
    </a>

    <a href="index.php" class="btn">
        Continue Shopping
    </a>

</div>

</body>
</html>
