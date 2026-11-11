
<?php
session_start();
include "includes/ecommerce_db.php";

// Require login before checkout
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php?next=checkout");
    exit;
}

if (empty($_SESSION["cart"])) {
    header("Location: cart.php");
    exit;
}

// Get customer details
$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT full_name, email, phone, address
     FROM users WHERE user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("Customer account not found.");
}

// Calculate cart total using current database prices
$items = [];
$total = 0;

foreach ($_SESSION["cart"] as $id => $quantity) {

    $stmt = $conn->prepare(
        "SELECT product_id, name, price, stock, status
         FROM products WHERE product_id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $product = $stmt->get_result()->fetch_assoc();

    if (
        !$product ||
        $product["status"] != "Available" ||
        $product["stock"] < $quantity
    ) {
        die("One or more products are unavailable or have insufficient stock. Please return to your cart and update it.");
    }

    $product["quantity"] = $quantity;
    $product["subtotal"] = $product["price"] * $quantity;

    $total += $product["subtotal"];
    $items[] = $product;
}

$error = $_SESSION["checkout_error"] ?? "";
unset($_SESSION["checkout_error"]);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout - PawHub</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        .checkout-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 25px;
        }

        .checkout-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
        }

        .checkout-box input,
        .checkout-box textarea {
            width: 100%;
            padding: 12px;
            margin: 8px 0 18px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .checkout-total {
            color: #ff7a45;
            font-size: 23px;
            font-weight: bold;
        }

        .payment-note {
            background: #fff0e8;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        @media(max-width: 700px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<header class="shop-header">
    <a href="index.php" class="shop-logo">🐾 PawHub</a>

    <nav class="shop-nav">
        <a href="index.php">Home</a>
        <a href="cart.php">Cart</a>
        <a href="orders.php">My Orders</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="checkout-container">

    <div class="checkout-box">

        <h1>Checkout</h1>
        <p>Enter your delivery information.</p>

        <?php if ($error != ""): ?>
            <p class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </p>
        <?php endif; ?>

        <form action="payment.php" method="POST">

            <label>Full Name</label>
            <input type="text" name="name"
                   value="<?php echo htmlspecialchars($user["full_name"]); ?>"
                   required>

            <label>Email</label>
            <input type="email"
                   value="<?php echo htmlspecialchars($user["email"]); ?>"
                   readonly>

            <label>Phone Number</label>
            <input type="text" name="phone"
                   value="<?php echo htmlspecialchars($user["phone"] ?? ""); ?>"
                   required>

            <label>Delivery Address</label>
            <textarea name="address" rows="4" required><?php
                echo htmlspecialchars($user["address"] ?? "");
            ?></textarea>

            <div class="payment-note">
            <strong>Payment Method: eSewa (Demo)</strong>
            <p>You will be redirected to a simulated wallet payment screen where you can enter your demo mobile number and MPIN.</p>
            </div>

            <button type="submit" class="btn btn-primary">
                Pay with eSewa (Demo)
            </button>

        </form>

    </div>

    <div class="checkout-box">

        <h2>Order Summary</h2>

        <?php foreach ($items as $item): ?>

            <p>
                <?php echo htmlspecialchars($item["name"]); ?>
                × <?php echo $item["quantity"]; ?>

                <br>

                <strong>
                    NPR <?php echo number_format($item["subtotal"], 2); ?>
                </strong>
            </p>

            <hr>

        <?php endforeach; ?>

        <h2 class="checkout-total">
            Total: NPR <?php echo number_format($total, 2); ?>
        </h2>

    </div>

</div>

</body>
</html>
