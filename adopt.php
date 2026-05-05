<?php session_start(); ?>
<?php include "includes/db.php"; ?>
<?php
// Require login to apply for adoption
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$user_id = (int) $_SESSION["user_id"];

// Cast pet id to int to avoid SQL injection via ?id=
$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$result = $conn->query("SELECT * FROM pets WHERE pet_id = $id");
$pet = $result->fetch_assoc();

if (!$pet) {
    header("Location: index.php");
    exit;
}

$submitted = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name   = $conn->real_escape_string($_POST["name"]);
    $email  = $conn->real_escape_string($_POST["email"]);
    $phone  = $conn->real_escape_string($_POST["phone"]);
    $home   = $conn->real_escape_string($_POST["home"]);
    $reason = $conn->real_escape_string($_POST["reason"]);

    $conn->query("INSERT INTO applications (user_id, pet_id, applicant_name, applicant_email, applicant_phone, home_type, reason)
                  VALUES ($user_id, $id, '$name', '$email', '$phone', '$home', '$reason')");

    $submitted = true;
}
?>
<?php include "includes/header.php"; ?>

<div class="form-box wide">
    <?php if ($submitted) { ?>
        <div class="alert alert-success">
            <h2 style="text-align: center; color: #155724;">✓ Application Submitted!</h2>
            <p style="text-align: center; margin-top: 10px;">
                Thank you, <strong><?php echo $name; ?></strong>!<br>
                Your application to adopt <strong><?php echo $pet["name"]; ?></strong> has been received.<br>
                The shelter will contact you at <?php echo $email; ?> within a few days.
            </p>
            <p style="text-align: center; margin-top: 20px;">
                <a href="profile.php" class="btn">View My Applications</a>
                <a href="index.php" class="btn btn-primary">Browse More Pets</a>
            </p>
        </div>
    <?php } else { ?>

        <h2>Adopt <?php echo $pet["name"]; ?></h2>
        <p style="text-align: center; color: #666; margin-bottom: 20px;">
            Fill in the form below to apply for adoption.
        </p>

        <form method="POST" action="adopt.php?id=<?php echo $pet["pet_id"]; ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" required>
                </div>
                <div class="form-group">
                    <label>Home Type</label>
                    <select name="home" required>
                        <option value="">-- Select --</option>
                        <option>Apartment</option>
                        <option>House with garden</option>
                        <option>House without garden</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Why do you want to adopt <?php echo $pet["name"]; ?>?</label>
                <textarea name="reason" required></textarea>
            </div>
            <div class="form-group">
                <label>ID Proof (Citizenship / Driver's License / Passport)</label>
                <input type="file" name="id_proof" accept=".jpg,.jpeg,.png,.pdf">
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <a href="pet-detail.php?id=<?php echo $pet["pet_id"]; ?>" class="btn">Cancel</a>
                <button type="submit" class="btn btn-primary">Submit Application</button>
            </div>
        </form>

    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>