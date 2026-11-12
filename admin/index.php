
<?php
session_start();
include "../includes/ecommerce_db.php";

// Protect the admin dashboard
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit;
}

// Dashboard statistics
$products = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc();
$customers = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'user'")->fetch_assoc();
$orders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc();

$sales = $conn->query("
    SELECT COALESCE(SUM(total_amount), 0) AS total
    FROM orders
    WHERE order_status != 'Cancelled'
")->fetch_assoc();

// Recent orders
$recent_orders = $conn->query("
    SELECT orders.*, users.full_name
    FROM orders
    LEFT JOIN users ON orders.user_id = users.user_id
    ORDER BY orders.created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - PawHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            background: #f5f5f5;
            font-family: Arial, sans-serif;
        }

        .admin-container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .admin-header h1 {
            color: #ff7a45;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #ddd;
        }

        .stat-card h3 {
            color: #666;
            margin-bottom: 10px;
        }

        .stat-card p {
            font-size: 28px;
            font-weight: bold;
            color: #ff7a45;
        }

        .admin-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-top: 25px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #fff0e9;
        }

        .logout-btn {
            background: #ff7a45;
            color: white;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>

<body>
<div style="margin: 20px;">
    <a href="orders.php" style="
        display: inline-block;
        background: #f28c28;
        color: white;
        padding: 12px 20px;
        text-decoration: none;
        border-radius: 5px;
    ">
        Manage Orders
    </a>
</div>
<div class="admin-container">

    <div class="admin-header">
        <div>
            <h1>PawHub Admin Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION["full_name"]); ?>!</p>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="stats">

        <div class="stat-card">
            <h3>Total Products</h3>
            <p><?php echo $products["total"]; ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Customers</h3>
            <p><?php echo $customers["total"]; ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Orders</h3>
            <p><?php echo $orders["total"]; ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Sales</h3>
            <p>Rs. <?php echo number_format($sales["total"], 2); ?></p>
        </div>

    </div>

    <div class="admin-section">

        <h2>Recent Orders</h2>

        <table>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Date</th>
            </tr>

            <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>

                <?php while ($order = $recent_orders->fetch_assoc()): ?>

                    <tr>
                        <td>#<?php echo $order["order_id"]; ?></td>

                        <td>
                            <?php echo htmlspecialchars($order["full_name"] ?? "Unknown"); ?>
                        </td>

                        <td>
                            Rs. <?php echo number_format($order["total_amount"], 2); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($order["payment_method"] ?? "N/A"); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($order["order_status"] ?? "Pending"); ?>
                        </td>

                        <td>
                            <?php echo date("d M Y", strtotime($order["created_at"])); ?>
                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="6">No orders found.</td>
                </tr>

            <?php endif; ?>

        </table>

    </div>

</div>

</body>
</html>
