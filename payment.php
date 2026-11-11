
<?php
session_start();
include "includes/ecommerce_db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php?next=checkout");
    exit;
}

// Receive delivery details from checkout
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address = trim($_POST["address"] ?? "");
    $phone = trim($_POST["phone"] ?? "");

    if ($address == "" || $phone == "") {
        $_SESSION["checkout_error"] = "Please enter your delivery details.";
        header("Location: checkout.php");
        exit;
    }

    $_SESSION["delivery_address"] = $address;
    $_SESSION["delivery_phone"] = $phone;
}

if (
    empty($_SESSION["delivery_address"]) ||
    empty($_SESSION["delivery_phone"]) ||
    empty($_SESSION["cart"])
) {
    header("Location: checkout.php");
    exit;
}

// Calculate total using database prices
$total = 0;

foreach ($_SESSION["cart"] as $id => $quantity) {
    $stmt = $conn->prepare(
        "SELECT price, stock, status FROM products WHERE product_id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (
        !$product ||
        $product["status"] != "Available" ||
        $product["stock"] < $quantity
    ) {
        $_SESSION["checkout_error"] = "A product is unavailable or has insufficient stock. Please update your cart.";
        header("Location: cart.php");
        exit;
    }

    $total += $product["price"] * $quantity;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>eSewa Demo Payment - PawHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f2f5f3;
            color: #333;
        }

        .payment-header {
            background: white;
            padding: 18px 6%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .payment-header a {
            color: #ff7a45;
            font-size: 25px;
            font-weight: bold;
            text-decoration: none;
        }

        .payment-container {
            max-width: 430px;
            margin: 45px auto;
            padding: 20px;
        }

        .payment-card {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.09);
        }

        .payment-top {
            background: #55a630;
            color: white;
            text-align: center;
            padding: 25px;
        }

        .payment-logo {
            font-size: 30px;
            font-weight: bold;
        }

        .payment-top p {
            margin-bottom: 0;
        }

        .payment-body {
            padding: 28px;
        }

        .amount {
            text-align: center;
            margin-bottom: 25px;
        }

        .amount small {
            color: #777;
        }

        .amount h1 {
            color: #55a630;
            margin: 8px 0;
        }

        .payment-body label {
            display: block;
            margin: 17px 0 7px;
            font-weight: bold;
        }

        .payment-body input {
            width: 100%;
            padding: 13px;
            border: 1px solid #ccc;
            border-radius: 7px;
            font-size: 16px;
        }

        .pay-button {
            width: 100%;
            padding: 14px;
            margin-top: 25px;
            border: none;
            border-radius: 7px;
            background: #55a630;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .pay-button:hover {
            background: #438c25;
        }

        .demo-note {
            background: #f0f7ec;
            border: 1px solid #d5e9ca;
            padding: 12px;
            border-radius: 7px;
            font-size: 13px;
            margin-top: 20px;
            line-height: 1.6;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #555;
            text-decoration: none;
        }

        .security-note {
            text-align: center;
            color: #777;
            font-size: 12px;
            margin-top: 18px;
        }
    </style>
</head>

<body>

<header class="payment-header">
    <a href="index.php">🐾 PawHub</a>
</header>

<div class="payment-container">

    <div class="payment-card">

        <div class="payment-top">
            <div class="payment-logo">eSewa</div>
            <p>Mobile Wallet Payment (Demo)</p>
        </div>

        <div class="payment-body">
            <?php if (!empty($_SESSION["payment_error"])): ?>

            <p style="background:#ffe5e5; color:#c00; padding:12px; border-radius:6px;">
                <?php echo htmlspecialchars($_SESSION["payment_error"]); ?>
            </p>

            <?php unset($_SESSION["payment_error"]); ?>

             <?php endif; ?>

            <div class="amount">
                <small>Total payment amount</small>
                <h1>NPR <?php echo number_format($total, 2); ?></h1>
            </div>

            <form action="process-order.php" method="POST">

                <label>Mobile Number</label>

                <input
                    type="tel"
                    name="mobile"
                    placeholder="Enter your mobile number"
                    pattern="[0-9]{10}"
                    maxlength="10"
                    required
                >

                <label>MPIN</label>

                <input
                    type="password"
                    name="mpin"
                    placeholder="Enter your 4-digit demo MPIN"
                    pattern="[0-9]{4}"
                    maxlength="4"
                    inputmode="numeric"
                    required
                >

                <button type="submit" class="pay-button">
                    Confirm Payment
                </button>

            </form>

            <div class="demo-note">
                <strong>Demo payment only</strong><br>
                Test mobile: 9800000000<br>
                Test MPIN: 1234<br>
                This is not connected to the real eSewa service. No real payment will be made.
            </div>

        </div>
    </div>

    <a href="checkout.php" class="back-link">
        ← Back to checkout
    </a>

    <p class="security-note">
        PawHub demonstration payment system
    </p>

</div>

</body>
</html>
