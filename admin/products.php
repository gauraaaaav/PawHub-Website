
<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit;
}

include "../includes/ecommerce_db.php";

$message = "";

// Add Product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_product"])) {

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = (float) $_POST["price"];
    $stock = (int) $_POST["stock"];
    $category_id = !empty($_POST["category_id"])
        ? (int) $_POST["category_id"]
        : null;

    $image = "";

    // Upload product image
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {

        $allowed = ["image/jpeg", "image/png", "image/webp"];
        $fileType = mime_content_type($_FILES["image"]["tmp_name"]);

        if (in_array($fileType, $allowed) &&
            $_FILES["image"]["size"] <= 2 * 1024 * 1024) {

            $extension = pathinfo(
                $_FILES["image"]["name"],
                PATHINFO_EXTENSION
            );

            $fileName = uniqid("product_", true) . "." . $extension;
            $uploadPath = "../images/products/" . $fileName;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $uploadPath)) {
                $image = "images/products/" . $fileName;
            }
        }
    }

    if ($name != "" && $price > 0 && $stock >= 0) {

        $stmt = $conn->prepare(
            "INSERT INTO products
            (name, description, price, stock, category_id, image, status)
            VALUES (?, ?, ?, ?, ?, ?, 'Available')"
        );

        $stmt->bind_param(
            "ssdiis",
            $name,
            $description,
            $price,
            $stock,
            $category_id,
            $image
        );

        if ($stmt->execute()) {
            $message = "Product added successfully!";
        } else {
            $message = "Error adding product.";
        }

        $stmt->close();

    } else {
        $message = "Please enter valid product details.";
    }
}

// Get Categories
$categories = $conn->query("SELECT * FROM categories");

// Get Products
$products = $conn->query("
    SELECT products.*, categories.category_name
    FROM products
    LEFT JOIN categories
    ON products.category_id = categories.category_id
    ORDER BY products.product_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Products</title>
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .admin-container {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .product-form {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .product-form input,
        .product-form textarea,
        .product-form select {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px;
            box-sizing: border-box;
        }

        .product-form button {
            background: #f28c28;
            color: white;
            border: none;
            padding: 12px 20px;
            cursor: pointer;
            border-radius: 5px;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .product-table th,
        .product-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .product-table th {
            background: #f28c28;
            color: white;
        }

        .product-table img {
            width: 60px;
            height: 60px;
            object-fit: cover;
        }

        .action-button {
            display: inline-block;
            padding: 7px 10px;
            margin: 3px;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }

        .edit-button {
            background: #3498db;
            color: white;
        }

        .delete-button {
            background: #e74c3c;
            color: white;
        }

        .message {
            color: green;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

<div class="admin-container">

    <h1>Manage Products</h1>

    <p>
        <a href="index.php">Back to Dashboard</a>
    </p>

    <?php if ($message != ""): ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <!-- Add Product Form -->
    <div class="product-form">

        <h2>Add New Product</h2>

        <form method="POST" enctype="multipart/form-data">

            <label>Product Name</label>
            <input type="text" name="name" required>

            <label>Description</label>
            <textarea name="description" rows="4"></textarea>

            <label>Category</label>
            <select name="category_id">
                <option value="">Select Category</option>

                <?php while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $category['category_id']; ?>">
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </option>
                <?php endwhile; ?>

            </select>

            <label>Price (NPR)</label>
            <input type="number" name="price" min="0.01" step="0.01" required>

            <label>Stock</label>
            <input type="number" name="stock" min="0" required>

            <label>Product Image</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp">

            <button type="submit" name="add_product">
                Add Product
            </button>

        </form>

    </div>

    <!-- Product List -->
    <h2>All Products</h2>

    <table class="product-table">

        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php if ($products && $products->num_rows > 0): ?>

            <?php while ($product = $products->fetch_assoc()): ?>

                <tr>
                    <td><?php echo $product['product_id']; ?></td>

                    <td>
                        <?php if (!empty($product['image'])): ?>
                            <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="Product">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>

                    <td><?php echo htmlspecialchars($product['name']); ?></td>

                    <td>
                        <?php echo htmlspecialchars($product['category_name'] ?? 'No Category'); ?>
                    </td>

                    <td>Rs. <?php echo number_format($product['price'], 2); ?></td>

                    <td><?php echo $product['stock']; ?></td>

                    <td><?php echo htmlspecialchars($product['status']); ?></td>

                    <td>
                        <a class="action-button edit-button"
                           href="edit-product.php?id=<?php echo $product['product_id']; ?>">
                            Edit
                        </a>

                        <?php if ($product['status'] != 'Unavailable'): ?>

                            <form action="delete-product.php"
                                  method="POST"
                                  style="display:inline;"
                                  onsubmit="return confirm('Are you sure you want to make this product unavailable?');">

                                <input type="hidden"
                                       name="product_id"
                                       value="<?php echo $product['product_id']; ?>">

                                <button type="submit"
                                        class="action-button delete-button">
                                    Delete
                                </button>

                            </form>

                        <?php endif; ?>
                    </td>

                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>
                <td colspan="8">No products found.</td>
            </tr>

        <?php endif; ?>

    </table>

</div>

</body>
</html>
