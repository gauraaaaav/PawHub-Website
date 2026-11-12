
<?php
session_start();
include "../includes/ecommerce_db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location: products.php");
    exit;
}

$id = (int)$_GET["id"];
$message = "";

$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    die("Product not found.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = $_POST["price"];
    $stock = $_POST["stock"];
    $category_id = $_POST["category_id"];
    $status = $_POST["status"];
    $image = $product["image"];

    if ($name == "" || !is_numeric($price) || $price <= 0 ||
        filter_var($stock, FILTER_VALIDATE_INT) === false || $stock < 0) {

        $message = "Please enter valid product details.";

    } elseif (!in_array($status, ["Available", "Unavailable"])) {

        $message = "Invalid product status.";

    } else {

        if (!empty($_FILES["image"]["name"])) {

            $allowed = ["image/jpeg", "image/png", "image/webp"];
            $file_type = mime_content_type($_FILES["image"]["tmp_name"]);

            if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK ||
                !in_array($file_type, $allowed)) {

                $message = "Please upload a valid JPG, PNG or WEBP image.";

            } elseif ($_FILES["image"]["size"] > 2 * 1024 * 1024) {

                $message = "Image must be less than 2 MB.";

            } else {

                $extensions = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                $filename = uniqid("product_", true) . "." . $extensions[$file_type];
                $destination = "../images/products/" . $filename;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $destination)) {
                    $image = "images/products/" . $filename;
                } else {
                    $message = "Image upload failed.";
                }
            }
        }

        if ($message == "") {

            $category_id = ($category_id == "") ? null : (int)$category_id;

            $stmt = $conn->prepare("
                UPDATE products
                SET name = ?, description = ?, price = ?, stock = ?,
                    category_id = ?, image = ?, status = ?
                WHERE product_id = ?
            ");

            $stmt->bind_param(
                "ssdiissi",
                $name,
                $description,
                $price,
                $stock,
                $category_id,
                $image,
                $status,
                $id
            );

            if ($stmt->execute()) {
                header("Location: products.php");
                exit;
            } else {
                $message = "Could not update product.";
            }

            $stmt->close();
        }
    }
}

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product - PawHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background: #f5f5f5; }
        .container {
            max-width: 700px;
            margin: 30px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
        }
        input, textarea, select {
            width: 100%;
            padding: 11px;
            margin: 8px 0 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        textarea { min-height: 80px; }
        .preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            display: block;
            margin-bottom: 15px;
        }
        .back { color: #ff7a45; text-decoration: none; }
    </style>
</head>
<body>

<div class="container">

    <h1>Edit Product</h1>
    <p><a class="back" href="products.php">← Back to Products</a></p>

    <?php if ($message != ""): ?>
        <p class="alert alert-danger">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Product Name</label>
        <input type="text" name="name"
               value="<?php echo htmlspecialchars($product["name"]); ?>" required>

        <label>Description</label>
        <textarea name="description"><?php
            echo htmlspecialchars($product["description"] ?? "");
        ?></textarea>

        <label>Category</label>
        <select name="category_id">
            <option value="">Uncategorized</option>
            <?php while ($category = $categories->fetch_assoc()): ?>
                <option value="<?php echo $category["category_id"]; ?>"
                    <?php echo $product["category_id"] == $category["category_id"] ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($category["category_name"]); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Price (Rs.)</label>
        <input type="number" name="price" min="0.01" step="0.01"
               value="<?php echo $product["price"]; ?>" required>

        <label>Stock</label>
        <input type="number" name="stock" min="0"
               value="<?php echo $product["stock"]; ?>" required>

        <label>Current Image</label>
        <?php if (!empty($product["image"])): ?>
            <img class="preview"
                 src="../<?php echo htmlspecialchars($product["image"]); ?>"
                 alt="Current product image">
        <?php else: ?>
            <p>No image uploaded.</p>
        <?php endif; ?>

        <label>Choose New Image (optional)</label>
        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">

        <label>Status</label>
        <select name="status">
            <option value="Available" <?php echo $product["status"] == "Available" ? "selected" : ""; ?>>
                Available
            </option>
            <option value="Unavailable" <?php echo $product["status"] == "Unavailable" ? "selected" : ""; ?>>
                Unavailable
            </option>
        </select>

        <button type="submit" class="btn btn-primary">Save Changes</button>

    </form>

</div>

</body>
</html>
