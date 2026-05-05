<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$loggedIn = isset($_SESSION["user_id"]);
$isAdmin = $loggedIn && $_SESSION["role"] == "admin";

$currentPage = basename($_SERVER["PHP_SELF"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PawHub</title>
    <link rel="icon" type="image/png" href="images/favicon_io/apple-touch-icon.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <a href="index.php" class="logo">🐾PawHub</a>
            <nav class="nav">

                <!-- Always visible to everyone -->
                <a href="index.php" class="<?php if ($currentPage == 'index.php') echo 'active'; ?>">Home</a>
                <a href="about.php" class="<?php if ($currentPage == 'about.php') echo 'active'; ?>">About Us</a>
                <a href="contact.php" class="<?php if ($currentPage == 'contact.php') echo 'active'; ?>">Contact</a>
                <a href="donate.php" class="<?php if ($currentPage == 'donate.php') echo 'active'; ?>">Donate</a>

                <!-- Auth-specific links -->
                <?php if ($isAdmin) { ?>
                    <a href="admin.php" class="<?php if ($currentPage == 'admin.php') echo 'active'; ?>">Admin</a>
                    <a href="logout.php" class="btn">Logout</a>

                <?php } elseif ($loggedIn) { ?>
                    <a href="profile.php" class="<?php if ($currentPage == 'profile.php') echo 'active'; ?>">Profile</a>
                    <a href="logout.php" class="btn">Logout</a>

                <?php } else { ?>
                    <a href="login.php" class="btn">Login</a>
                    <a href="signup.php" class="btn btn-primary">Register</a>
                <?php } ?>

            </nav>
        </div>
    </header>
    <main class="container">