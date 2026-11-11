
<?php
session_start();
include "includes/ecommerce_db.php";

// Only logged-in customers can view orders
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT * FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders - PawHub</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        .orders-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        .order-card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .order-heading {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .order-total {
            color: #ff7a45;
            font-weight: bold;
            font-size: 20px;
        }

        .order-products {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #eee;
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

<div class="orders-container">

    <h1>My Orders 📦</h1>

    <?php if ($orders->num_rows == 0): ?>

        <div class="order-card">
            <h2>No orders yet!</h2>
            <p>Your purchases will appear here.</p>
            <a href="index.php" class="btn btn-primary">
                Start Shopping
            </a>
        </div>

    <?php else: ?>

        <?php while ($order = $orders->fetch_assoc()): ?>

            <div class="order-card">

                <div class="order-heading">
                    <div>
                        <h2>Order #<?php echo $order["order_id"]; ?></h2>

                        <p>
                            <?php echo date(
                                "d M Y, h:i A",
                                strtotime($order["created_at"])
                            ); ?>
                        </p>
                    </div>

                    <div>
                        <span class="badge">
                            <?php echo htmlspecialchars($order["order_status"]); ?>
                        </span>
                    </div>
                </div>

                <p>
                    <strong>Payment:</strong>
                    <?php echo htmlspecialchars($order["payment_method"]); ?>
                </p>

                <p>
                    <strong>Delivery Address:</strong>
                    <?php echo htmlspecialchars($order["delivery_address"]); ?>
                </p>

                <div class="order-products">

                    <strong>Products:</strong>

                    <?php
                    $order_id = $order["order_id"];

                    $itemStmt = $conn->prepare(
                        "SELECT product_name, quantity, price
                         FROM order_items
                         WHERE order_id = ?"
                    );

                    $itemStmt->bind_param("i", $order_id);
                    $itemStmt->execute();

                    $orderItems = $itemStmt->get_result();
                    ?>

                    <?php while ($item = $orderItems->fetch_assoc()): ?>

                        <p>
                            <?php echo htmlspecialchars($item["product_name"]); ?>
                            × <?php echo $item["quantity"]; ?>

                            — NPR <?php echo number_format(
                                $item["price"] * $item["quantity"],
                                2
                            ); ?>
                        </p>

                    <?php endwhile; ?>

                </div>

                <p class="order-total">
                    Total: NPR <?php echo number_format($order["total_amount"], 2); ?>
                </p>

            </div>

        <?php endwhile; ?>

    <?php endif; ?>

</div>

</body>
</html>
