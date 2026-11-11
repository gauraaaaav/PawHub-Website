
<?php
session_start();
include "includes/ecommerce_db.php";

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

$message = $_SESSION["cart_message"] ?? "";
unset($_SESSION["cart_message"]);

// Update or remove cart items
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["update"])) {

        foreach ($_POST["quantity"] as $id => $qty) {

            $id = intval($id);
            $qty = intval($qty);

            $stmt = $conn->prepare(
                "SELECT stock FROM products WHERE product_id = ?"
            );

            $stmt->bind_param("i", $id);
            $stmt->execute();

            $product = $stmt->get_result()->fetch_assoc();

            if (!$product || $qty <= 0) {
                unset($_SESSION["cart"][$id]);
            } else {
                $_SESSION["cart"][$id] = min($qty, $product["stock"]);
            }
        }

        $message = "Cart updated successfully.";
    }

    if (isset($_POST["remove"])) {
        $id = intval($_POST["remove"]);
        unset($_SESSION["cart"][$id]);
        $message = "Product removed from cart.";
    }
}

// Get current cart products from database
$items = [];
$total = 0;

foreach ($_SESSION["cart"] as $id => $quantity) {

    $stmt = $conn->prepare(
        "SELECT * FROM products WHERE product_id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $product = $stmt->get_result()->fetch_assoc();

    if (!$product || $product["status"] != "Available" || $product["stock"] <= 0) {
        unset($_SESSION["cart"][$id]);
        continue;
    }

    // Keep quantity within available stock
    if ($quantity > $product["stock"]) {
        $quantity = $product["stock"];
        $_SESSION["cart"][$id] = $quantity;
    }

    $product["quantity"] = $quantity;
    $product["subtotal"] = $product["price"] * $quantity;

    $total += $product["subtotal"];
    $items[] = $product;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart - PawHub</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        .cart-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        .cart-item {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .cart-item input {
            width: 65px;
            padding: 8px;
        }

        .cart-total {
            background: white;
            padding: 25px;
            text-align: right;
            border-radius: 10px;
        }

        .cart-price {
            color: #ff7a45;
            font-weight: bold;
        }

        .cart-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
    </style>
</head>

<body>

<header class="shop-header">
    <a href="index.php" class="shop-logo">🐾 PawHub</a>

    <nav class="shop-nav">
        <a href="index.php">Home</a>
        <a href="cart.php">Cart</a>

        <?php if (isset($_SESSION["user_id"])): ?>
            <a href="orders.php">My Orders</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="signup.php">Sign Up</a>
        <?php endif; ?>
    </nav>
</header>

<div class="cart-container">

    <h1>My Shopping Cart 🛒</h1>

    <?php if ($message != ""): ?>
        <p class="alert alert-info">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <?php if (count($items) == 0): ?>

        <div class="cart-item">
            <div>
                <h2>Your cart is empty!</h2>
                <p>Explore our products and add something for your pet.</p>
                <a href="index.php" class="btn btn-primary">
                    Continue Shopping
                </a>
            </div>
        </div>

    <?php else: ?>

        <form method="POST">

            <?php foreach ($items as $item): ?>

                <div class="cart-item">

                    <div>
                        <h3><?php echo htmlspecialchars($item["name"]); ?></h3>

                        <p class="cart-price">
                            NPR <?php echo number_format($item["price"], 2); ?>
                        </p>
                    </div>

                    <div>
                        <label>Quantity:</label>

                        <input type="number"
                               name="quantity[<?php echo $item["product_id"]; ?>]"
                               value="<?php echo $item["quantity"]; ?>"
                               min="1"
                               max="<?php echo $item["stock"]; ?>">
                    </div>

                    <div>
                        <strong>
                            NPR <?php echo number_format($item["subtotal"], 2); ?>
                        </strong>
                    </div>

                    <button type="submit"
                            name="remove"
                            value="<?php echo $item["product_id"]; ?>"
                            class="btn btn-danger">
                        Remove
                    </button>

                </div>

            <?php endforeach; ?>

            <button type="submit" name="update" class="btn btn-primary">
                Update Cart
            </button>

        </form>

        <div class="cart-total">

            <h2>
                Total: NPR <?php echo number_format($total, 2); ?>
            </h2>

            <a href="index.php" class="btn">
                Continue Shopping
            </a>

            <a href="checkout.php" class="btn btn-primary">
                Proceed to Checkout
            </a>

        </div>

    <?php endif; ?>

</div>

</body>
</html>
