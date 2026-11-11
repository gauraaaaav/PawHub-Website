
<?php
session_start();
include "includes/ecommerce_db.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm"];

    if ($name == "" || $email == "" || $password == "") {
        $error = "Please fill in all required fields.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }
    elseif (strlen($password) < 8) {
        $error = "Password must contain at least 8 characters.";
    }
    elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    }
    else {
        // Check whether email already exists
        $stmt = $conn->prepare(
            "SELECT user_id FROM users WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "This email is already registered.";
        }
        else {
            // Securely hash the password
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Register customer
            $stmt = $conn->prepare(
                "INSERT INTO users
                (full_name, email, password, phone, role)
                VALUES (?, ?, ?, ?, 'user')"
            );

            $stmt->bind_param(
                "ssss",
                $name,
                $email,
                $hashedPassword,
                $phone
            );

            if ($stmt->execute()) {
                $success = "Account created successfully! You can now log in.";
            }
            else {
                $error = "Something went wrong. Please try again.";
            }
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - PawHub</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

<div class="form-box">

    <h1>Create Account</h1>
    <p>Join PawHub and shop for your pets!</p>

    <?php if ($error != ""): ?>
        <p class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>

    <?php if ($success != ""): ?>
        <p class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
        </p>
    <?php endif; ?>

    <?php if ($success == ""): ?>

    <form method="POST">

        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone">
        </div>

        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Confirm Password *</label>
            <input type="password" name="confirm" required>
        </div>

        <button type="submit" class="btn btn-primary">
            Create Account
        </button>

    </form>

    <?php endif; ?>

</div>

</body>
</html>
