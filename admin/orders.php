
<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit;
}

include "../includes/ecommerce_db.php";

// Create CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Update Order Status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_status"])) {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        die("Invalid request. Please go back and try again.");
    }

    $order_id = (int) $_POST["order_id"];
    $status = $_POST["order_status"];

    $allowed_statuses = [
        "Pending",
        "Processing",
        "Shipped",
        "Delivered",
        "Cancelled"
    ];

    if (in_array($status, $allowed_statuses, true)) {

        $stmt = $conn->prepare(
            "UPDATE orders SET order_status = ? WHERE order_id = ?"
        );

        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: orders.php?updated=1");
    exit;
}

// Get All Orders
$orders = $conn->query("
    SELECT
        o.*,
        u.full_name,
        u.email,
        p.transaction_code,
        p.amount AS payment_amount,
        p.payment_method AS payment_method_detail,
        p.payment_status
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    LEFT JOIN payments p ON o.order_id = p.order_id
    ORDER BY o.created_at DESC
");

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html>
<head>

    <title>Manage Orders - PawHub</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>
        .orders-container {
            width: 92%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .order-card {
            background: white;
            padding: 22px;
            margin-bottom: 25px;
            border-radius: 10px;
            border: 1px solid #ddd;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }

        .order-details {
            margin: 15px 0;
            line-height: 1.8;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .order-table th,
        .order-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .order-table th {
            background: #f28c28;
            color: white;
        }

        .status-form {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .status-form select {
            padding: 9px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .status-form button {
            background: #f28c28;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .payment-info {
            background: #f8f8f8;
            padding: 12px;
            margin-top: 15px;
            border-radius: 5px;
        }

        @media (max-width: 650px) {
            .order-table {
                font-size: 12px;
            }

            .order-table th,
            .order-table td {
                padding: 6px;
            }
        }
    </style>

</head>

<body>

<div class="orders-container">

    <h1>Manage Orders</h1>

    <p>
        <a href="index.php">Back to Dashboard</a>
    </p>

    <?php if (isset($_GET["updated"])): ?>
        <div class="success-message">
            Order status updated successfully!
        </div>
    <?php endif; ?>

    <?php if ($orders && $orders->num_rows > 0): ?>

        <?php while ($order = $orders->fetch_assoc()): ?>

            <div class="order-card">

                <div class="order-header">

                    <div>
                        <h2>Order #<?php echo $order["order_id"]; ?></h2>

                        <p>
                            Date:
                            <?php echo date("d M Y, h:i A", strtotime($order["created_at"])); ?>
                        </p>
                    </div>

                    <div>
                        <strong>Status:</strong>
                        <?php echo clean($order["order_status"]); ?>
                    </div>

                </div>

                <!-- Customer Details -->
                <div class="order-details">

                    <h3>Customer Details</h3>

                    <strong>Name:</strong>
                    <?php echo clean($order["full_name"] ?? "Unknown"); ?>
                    <br>

                    <strong>Email:</strong>
                    <?php echo clean($order["email"] ?? "Not available"); ?>
                    <br>

                    <strong>Phone:</strong>
                    <?php echo clean($order["phone"]); ?>
                    <br>

                    <strong>Delivery Address:</strong>
                    <?php echo clean($order["delivery_address"]); ?>

                </div>

                <!-- Ordered Products -->
                <h3>Ordered Products</h3>

                <table class="order-table">

                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>

                    <?php
                    $itemStmt = $conn->prepare(
                        "SELECT product_name, quantity, price
                         FROM order_items
                         WHERE order_id = ?"
                    );

                    $itemStmt->bind_param("i", $order["order_id"]);
                    $itemStmt->execute();

                    $items = $itemStmt->get_result();

                    while ($item = $items->fetch_assoc()):
                    ?>

                        <tr>
                            <td><?php echo clean($item["product_name"]); ?></td>

                            <td><?php echo (int)$item["quantity"]; ?></td>

                            <td>
                                Rs. <?php echo number_format($item["price"], 2); ?>
                            </td>

                            <td>
                                Rs. <?php
                                echo number_format(
                                    $item["price"] * $item["quantity"],
                                    2
                                );
                                ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                    <?php $itemStmt->close(); ?>

                </table>

                <h3>
                    Total: Rs.
                    <?php echo number_format($order["total_amount"], 2); ?>
                </h3>

                <!-- Payment Details -->
                <div class="payment-info">

                    <h3>Payment Details</h3>

                    <strong>Payment Method:</strong>
                    <?php echo clean($order["payment_method_detail"] ?? $order["payment_method"] ?? "Not available"); ?>
                    <br>

                    <strong>Transaction Code:</strong>
                    <?php echo clean($order["transaction_code"] ?? "Not available"); ?>
                    <br>

                    <strong>Payment Amount:</strong>

                    <?php if ($order["payment_amount"] !== null): ?>
                        Rs. <?php echo number_format($order["payment_amount"], 2); ?>
                    <?php else: ?>
                        Not available
                    <?php endif; ?>

                    <br>

                    <strong>Payment Status:</strong>
                    <?php echo clean($order["payment_status"] ?? "Not available"); ?>

                </div>

                <!-- Update Status -->
                <form method="POST" class="status-form">

                    <input type="hidden" name="csrf_token"
                           value="<?php echo clean($_SESSION["csrf_token"]); ?>">

                    <input type="hidden" name="order_id"
                           value="<?php echo (int)$order["order_id"]; ?>">

                    <label>Update Order Status:</label>

                    <select name="order_status" required>

                        <?php
                        $statuses = [
                            "Pending",
                            "Processing",
                            "Shipped",
                            "Delivered",
                            "Cancelled"
                        ];

                        foreach ($statuses as $status):
                        ?>

                            <option value="<?php echo $status; ?>"
                                <?php
                                if ($order["order_status"] == $status) {
                                    echo "selected";
                                }
                                ?>>
                                <?php echo $status; ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <button type="submit" name="update_status">
                        Update Status
                    </button>

                </form>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No orders have been placed yet.</p>

    <?php endif; ?>

</div>

</body>
</html>
