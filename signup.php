<?php
session_start();
include "includes/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST["name"]);
    $email = $conn->real_escape_string($_POST["email"]);
    $password = $conn->real_escape_string($_POST["password"]);
    $confirm = $conn->real_escape_string($_POST["confirm"]);
    $phone = $conn->real_escape_string($_POST["phone"]);

    if ($password != $confirm) {
        $error = "Passwords do not match";
    } else {
        // Check if email already exists
        $check = $conn->query("SELECT user_id FROM users WHERE email = '$email'");

        if ($check->num_rows > 0) {
            $error = "An account with this email already exists";
        } else {
            $conn->query("INSERT INTO users (full_name, email, password, phone, role)
                          VALUES ('$name', '$email', '$password', '$phone', 'user')");

            // Auto-login
            $_SESSION["user_id"] = $conn->insert_id;
            $_SESSION["full_name"] = $name;
            $_SESSION["email"] = $email;
            $_SESSION["role"] = "user";

            header("Location: profile.php");
            exit;
        }
    }
}
?>

<?php include "includes/header.php"; ?>

<div class="form-box">
    <div class="login-monkey">
        <img src="images/monkey1.jpg" alt="" id="monkey">
    </div>
    <h2>Create Account</h2>
    <p style="text-align: center; color: #666; margin-bottom: 20px;">Join PawHub and find your forever friend!</p>

    <?php if ($error) { ?>
        <div class="alert alert-info" style="background-color: #f8d7da; color: #721c24;">
            <?php echo $error; ?>
        </div>
    <?php } ?>

    <form method="POST" action="signup.php">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm" required>
            </div>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="tel" name="phone">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Sign Up</button>
    </form>

    <p class="form-foot">
        Already have an account? <a href="login.php">Login</a>
    </p>
</div>
<script>
    const monkey = document.getElementById('monkey');
    const passwordFields = document.querySelectorAll('input[type="password"]');

    // Watch BOTH password and confirm fields
    passwordFields.forEach(field => {
        field.addEventListener('focus', () => {
            monkey.src = 'images/monkey2.jpg';
        });
        field.addEventListener('blur', () => {
            monkey.src = 'images/monkey1.jpg';
        });
    });
</script>    
<?php include "includes/footer.php"; ?>