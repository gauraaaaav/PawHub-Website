
<?php
session_start();
include "includes/ecommerce_db.php";

$id = intval($_GET["id"] ?? 0);

$stmt = $conn->prepare(
    "SELECT products.*, categories.category_name
     FROM products
     LEFT JOIN categories
     ON products.category_id = categories.category_id
     WHERE products.product_id = ? AND products.status = 'Available'"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($product["name"]); ?> - PawHub</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        .detail-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            display: flex;
            gap: 40px;
            background: white;
            border-radius: 12px;
            flex-wrap: wrap;
        }

        .detail-image {
            flex: 1;
            min-width: 250px;
            min-height: 300px;
            background: #fff0e8;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 100px;
            border-radius: 10px;
            overflow: hidden;
        }

        .detail-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .detail-info {
            flex: 1;
            min-width: 250px;
            padding: 20px;
        }

        .detail-info h1 {
            margin-top: 0;
        }

        .detail-price {
            color: #ff7a45;
            font-size: 26px;
            font-weight: bold;
            margin: 20px 0;
        }

        .detail-info input {
            width: 70px;
            padding: 10px;
            margin: 15px 0;
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

<div class="detail-container">

    <div class="detail-image">
        <?php if (!empty($product["image"]) && file_exists("images/" . $product["image"])): ?>
            <img src="images/<?php echo htmlspecialchars($product["image"]); ?>"
                 alt="<?php echo htmlspecialchars($product["name"]); ?>">
        <?php else: ?>
            🐾
        <?php endif; ?>
    </div>

    <div class="detail-info">

        <p><?php echo htmlspecialchars($product["category_name"] ?? "Pet Product"); ?></p>

        <h1><?php echo htmlspecialchars($product["name"]); ?></h1>

        <p><?php echo htmlspecialchars($product["description"]); ?></p>

        <div class="detail-price">
            NPR <?php echo number_format($product["price"], 2); ?>
        </div>

        <p>
            <?php echo $product["stock"] > 0
                ? "Available stock: " . $product["stock"]
                : "Currently out of stock"; ?>
        </p>

        <?php if ($product["stock"] > 0): ?>

            <form action="add-to-cart.php" method="POST">

                <input type="hidden" name="product_id"
                       value="<?php echo $product["product_id"]; ?>">

                <label>Quantity:</label><br>

                <input type="number" name="quantity"
                       value="1" min="1"
                       max="<?php echo $product["stock"]; ?>">

                <br>

                <button type="submit" class="btn btn-primary">
                    Add to Cart
                </button>

            </form>

        <?php endif; ?>

        <br>
        <a href="index.php">← Continue Shopping</a>

    </div>

</div>

</body>
</html>
