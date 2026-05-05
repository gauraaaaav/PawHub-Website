<?php
session_start();
include "includes/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST["email"]);
    $password = $_POST["password"];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    $user = $result->fetch_assoc();

    if ($user && $user["password"] == $password) {
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["full_name"] = $user["full_name"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["role"] = $user["role"];

        if ($user["role"] == "admin") {
            header("Location: admin.php");
        } else {
            header("Location: profile.php");
        }
        exit;
    } else {
        $error = "Invalid email or password";
    }
}
?>

<?php include "includes/header.php"; ?>

<div class="form-box">
    <div class="form-box">
    <!-- Cute monkey that covers eyes when typing password -->
    <div class="login-monkey">
        <img src="images/monkey1.jpg" alt="" id="monkey">
    </div>

    
    <h2>Login</h2>
    <p style="text-align: center; color: #666; margin-bottom: 20px;">Welcome back! Please login to your account.</p>

    <?php if ($error) { ?>
        <div class="alert alert-info" style="background-color: #f8d7da; color: #721c24;">
            <?php echo $error; ?>
        </div>
    <?php } ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
    </form>

    <p class="form-foot">
        Don't have an account? <a href="signup.php">Sign Up</a>
    </p>

    
</div>

<script>
    const monkey = document.getElementById('monkey');
    const passwordField = document.querySelector('input[name="password"]');

    // When user clicks/focuses on password field → cover eyes
    passwordField.addEventListener('focus', () => {
        monkey.src = 'images/monkey2.jpg';
    });

    // When user clicks away → uncover eyes
    passwordField.addEventListener('blur', () => {
        monkey.src = 'images/monkey1.jpg';
    });
</script>
<?php include "includes/footer.php"; ?>