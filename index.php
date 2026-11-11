
<?php
session_start();
include "includes/ecommerce_db.php";

// Get search and category filters
$search = trim($_GET["search"] ?? "");
$category = $_GET["category"] ?? "";

// Get all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY category_id");

// Get products
$sql = "SELECT products.*, categories.category_name
        FROM products
        LEFT JOIN categories
        ON products.category_id = categories.category_id
        WHERE products.status = 'Available'";

if ($search != "") {
    $sql .= " AND (products.name LIKE ? OR products.description LIKE ?)";
}

if ($category != "" && is_numeric($category)) {
    $sql .= " AND products.category_id = ?";
}

$sql .= " ORDER BY products.product_id DESC";

$stmt = $conn->prepare($sql);

if ($search != "" && $category != "" && is_numeric($category)) {
    $keyword = "%" . $search . "%";
    $stmt->bind_param("ssi", $keyword, $keyword, $category);
}
elseif ($search != "") {
    $keyword = "%" . $search . "%";
    $stmt->bind_param("ss", $keyword, $keyword);
}
elseif ($category != "" && is_numeric($category)) {
    $stmt->bind_param("i", $category);
}

$stmt->execute();
$result = $stmt->get_result();

$totalProducts = $result->num_rows;
?>

<!DOCTYPE html>
<html>
<head>
    <title>PawHub - Pet Supplies Store</title>

    <link rel="stylesheet" href="css/style.css">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            background: #f8f8f8;
            font-family: Arial, sans-serif;
        }

        .shop-header {
            background: white;
            padding: 18px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .shop-logo {
            color: #ff7a45;
            font-size: 27px;
            font-weight: bold;
            text-decoration: none;
        }

        .shop-nav {
            display: flex;
            gap: 22px;
            flex-wrap: wrap;
            align-items: center;
        }

        .shop-nav a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
        }

        .shop-nav a:hover {
            color: #ff7a45;
        }

        .shop-hero {
            background: linear-gradient(120deg, #fff0e8, #ffe0cc);
            padding: 75px 20px;
            text-align: center;
        }

        .shop-hero h1 {
            font-size: 42px;
            color: #333;
            margin-bottom: 15px;
        }

        .shop-hero p {
            color: #666;
            font-size: 17px;
        }

        .shop-hero .btn {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
        }

        .shop-container {
            max-width: 1200px;
            margin: auto;
            padding: 35px 20px;
        }

        .shop-section-title {
            text-align: center;
            margin-bottom: 25px;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 18px;
            margin-bottom: 45px;
        }

        .category-card {
            background: white;
            padding: 25px 15px;
            text-align: center;
            border-radius: 12px;
            text-decoration: none;
            color: #333;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: 0.2s;
        }

        .category-card:hover {
            transform: translateY(-4px);
            color: #ff7a45;
        }

        .category-icon {
            font-size: 35px;
            margin-bottom: 10px;
        }

        .shop-search {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .shop-search input {
            flex: 1;
            min-width: 180px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .shop-search button {
            border: none;
            cursor: pointer;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 22px;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.07);
            transition: 0.2s;
        }

        .product-card:hover {
            transform: translateY(-4px);
        }

        .product-image {
            height: 190px;
            background: #fff0e8;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            font-size: 65px;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            padding: 18px;
        }

        .product-info h3 {
            margin: 0 0 8px;
        }

        .product-category {
            color: #888;
            font-size: 13px;
        }

        .product-price {
            color: #ff7a45;
            font-size: 20px;
            font-weight: bold;
            margin: 12px 0;
        }

        .product-stock {
            font-size: 13px;
            color: #666;
        }

        .shop-footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 25px;
            margin-top: 50px;
        }

        .no-products {
            background: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
        }

        @media(max-width: 600px) {
            .shop-hero h1 {
                font-size: 30px;
            }

            .shop-header {
                justify-content: center;
            }

            .shop-nav {
                justify-content: center;
            }
        }
    </style>
</head>

<body>

<!-- HEADER -->
<header class="shop-header">

    <a href="index.php" class="shop-logo">🐾 PawHub</a>

    <nav class="shop-nav">
        <a href="index.php">Home</a>
        <a href="#products">Products</a>
        <a href="#categories">Categories</a>
        <a href="cart.php">Cart</a>

        <?php if (isset($_SESSION["user_id"])): ?>

            <a href="orders.php">My Orders</a>

            <?php if ($_SESSION["role"] == "admin"): ?>
                <a href="admin/dashboard.php">Admin</a>
            <?php endif; ?>

            <a href="logout.php">Logout</a>

        <?php else: ?>

            <a href="login.php">Login</a>
            <a href="signup.php">Sign Up</a>

        <?php endif; ?>
    </nav>

</header>


<!-- HERO SECTION -->
<section class="shop-hero">

    <h1>Everything Your Pet Needs!</h1>

    <p>
        Discover quality food, toys, accessories and care products
        for your beloved pets.
    </p>

    <a href="#products" class="btn btn-primary">
        Shop Now
    </a>

</section>


<div class="shop-container">

    <!-- CATEGORIES -->
    <section id="categories">

        <h2 class="shop-section-title">Shop By Category</h2>

        <div class="category-grid">

            <?php
            $icons = [
                "Pet Food" => "🥫",
                "Toys" => "🎾",
                "Accessories" => "🐾",
                "Grooming" => "🛁",
                "Health and Care" => "💊"
            ];

            while ($cat = $categories->fetch_assoc()):
            ?>

                <a class="category-card"
                   href="index.php?category=<?php echo $cat['category_id']; ?>#products">

                    <div class="category-icon">
                        <?php echo $icons[$cat["category_name"]] ?? "🐾"; ?>
                    </div>

                    <strong>
                        <?php echo htmlspecialchars($cat["category_name"]); ?>
                    </strong>

                </a>

            <?php endwhile; ?>

        </div>

    </section>


    <!-- PRODUCTS -->
    <section id="products">

        <h2 class="shop-section-title">Our Products</h2>

        <!-- SEARCH -->
        <form class="shop-search" method="GET" action="index.php#products">

            <input
                type="text"
                name="search"
                placeholder="Search for pet products..."
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <?php if ($category != ""): ?>
                <input type="hidden" name="category"
                       value="<?php echo htmlspecialchars($category); ?>">
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">
                Search
            </button>

            <a href="index.php" class="btn">
                Reset
            </a>

        </form>


        <p>
            Showing <?php echo $totalProducts; ?>
            product<?php echo $totalProducts == 1 ? "" : "s"; ?>
        </p>


        <?php if ($totalProducts == 0): ?>

            <div class="no-products">
                <h3>No products found!</h3>
                <p>Try another search or browse all products.</p>
                <a href="index.php" class="btn btn-primary">
                    View All Products
                </a>
            </div>

        <?php else: ?>

            <div class="product-grid">

                <?php while ($product = $result->fetch_assoc()): ?>

                    <div class="product-card">

                        <div class="product-image">

                            <?php
                            $image = $product["image"];

                            if (
                                !empty($image) &&
                                file_exists("images/" . $image)
                            ):
                            ?>

                                <img
                                    src="images/<?php echo htmlspecialchars($image); ?>"
                                    alt="<?php echo htmlspecialchars($product["name"]); ?>"
                                >

                            <?php else: ?>

                                🐾

                            <?php endif; ?>

                        </div>


                        <div class="product-info">

                            <span class="product-category">
                                <?php echo htmlspecialchars($product["category_name"] ?? "Pet Product"); ?>
                            </span>

                            <h3>
                                <?php echo htmlspecialchars($product["name"]); ?>
                            </h3>

                            <p>
                                <?php echo htmlspecialchars($product["description"]); ?>
                            </p>

                            <div class="product-price">
                                NPR <?php echo number_format($product["price"], 2); ?>
                            </div>

                            <div class="product-stock">
                                <?php echo $product["stock"] > 0
                                    ? "In Stock: " . $product["stock"]
                                    : "Out of Stock"; ?>
                            </div>
                            <br>

                            <a href="product-detail.php?id=<?php echo $product['product_id']; ?>"
                            class="btn">
                                View Details
                            </a>

                            <?php if ($product["stock"] > 0): ?>

                                <form action="add-to-cart.php" method="POST">

                                    <input type="hidden" name="product_id"
                                        value="<?php echo $product['product_id']; ?>">

                                    <input type="hidden" name="quantity" value="1">

                                    <button type="submit" class="btn btn-primary">
                                        Add to Cart
                                    </button>

                                </form>

                            <?php endif; ?>

                            

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

        <?php endif; ?>

    </section>

</div>


<!-- FOOTER -->
<footer class="shop-footer">
    <h3>🐾 PawHub</h3>
    <p>Your one-stop shop for happy and healthy pets.</p>
    <p>© <?php echo date("Y"); ?> PawHub. All Rights Reserved.</p>
</footer>

</body>
</html>
