<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();

$applications = $conn->query("SELECT applications.*, pets.name AS pet_name, pets.species, pets.shelter
                              FROM applications
                              JOIN pets ON applications.pet_id = pets.pet_id
                              WHERE applications.user_id = $user_id
                              ORDER BY applications.submitted_at DESC");
?>


<?php include "includes/header.php"; ?>

<h1 class="page-title">Welcome, <?php echo $user["full_name"]; ?>! 👋</h1>

<div class="form-box wide" style="margin-top: 20px;">
    <h2 style="text-align: left;">Profile Information</h2>
    <div class="form-row">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" value="<?php echo $user["full_name"]; ?>" disabled>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" value="<?php echo $user["email"]; ?>" disabled>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Phone</label>
            <input type="tel" value="<?php echo $user["phone"]; ?>" disabled>
        </div>
        <div class="form-group">
            <label>Address</label>
            <input type="text" value="<?php echo $user["address"]; ?>" disabled>
        </div>
    </div>
</div>

<h2 class="page-title">My Adoption Applications</h2>

<?php if ($applications->num_rows == 0) { ?>
    <div class="alert alert-info">
        You haven't submitted any adoption applications yet. <a href="index.php">Browse pets</a> to get started!
    </div>
<?php } else { ?>
    <div class="application-list">
        <?php while ($app = $applications->fetch_assoc()) { ?>
            <div class="application-card application-<?php echo strtolower($app["status"]); ?>">
                <!-- Top row -->
                <div class="app-header">
                    <div>
                        <strong>PAW-<?php echo str_pad($app["app_id"], 5, "0", STR_PAD_LEFT); ?></strong>
                        <span style="color: #888; margin-left: 10px;">
                            <?php echo $app["pet_name"]; ?> (<?php echo $app["species"]; ?>)
                        </span>
                    </div>
                    <div>
                        <small style="color: #888;"><?php echo date("M j, Y", strtotime($app["submitted_at"])); ?></small>
                        <?php if ($app["status"] == "Approved") { ?>
                            <span class="badge badge-approved">✓ Approved</span>
                        <?php } elseif ($app["status"] == "Rejected") { ?>
                            <span class="badge badge-rejected">✗ Rejected</span>
                        <?php } else { ?>
                            <span class="badge badge-pending">⏳ Pending</span>
                        <?php } ?>
                    </div>
                </div>

                <!-- Status-specific message -->
                <?php if ($app["status"] == "Approved") { ?>
                    <div class="app-body app-body-approved">
                        <h3>🎉 Congratulations! Your application has been approved.</h3>
                        <p style="margin-bottom: 15px;">You're one step closer to bringing <strong><?php echo $app["pet_name"]; ?></strong> home!</p>

                        <h4>What to do next:</h4>
                        <ol style="margin-left: 20px; margin-bottom: 15px;">
                            <li>Visit <strong><?php echo $app["shelter"]; ?></strong> within the next 7 days</li>
                            <li>Bring a valid ID (citizenship or driver's license)</li>
                            <li>Bring address proof (utility bill or rental agreement)</li>
                            <li>Adoption fee: <strong>Rs. 500</strong> (covers vaccination & deworming)</li>
                            <li>Sign the adoption agreement at the shelter</li>
                            <li>Welcome <?php echo $app["pet_name"]; ?> into your home! 🏠</li>
                        </ol>

                        <p style="font-size: 14px; color: #155724;">
                            <strong>Questions?</strong> Contact <?php echo $app["shelter"]; ?> directly or
                            <a href="contact.php">reach out to us</a>.
                        </p>
                    </div>

                <?php } elseif ($app["status"] == "Rejected") { ?>
                    <div class="app-body app-body-rejected">
                        <h3>Application not accepted this time</h3>
                        <p>Thank you for your interest in <strong><?php echo $app["pet_name"]; ?></strong>. Unfortunately, your application wasn't selected on this occasion.</p>
                        <p style="margin-top: 10px;">
                            This often happens when multiple families apply for the same pet. We encourage you to
                            <a href="index.php">browse other adoptable pets</a> — there are many wonderful animals still looking for a home.
                        </p>
                    </div>

                <?php } else { ?>
                    <div class="app-body app-body-pending">
                        <p>⏳ Your application is being reviewed by the shelter. We'll notify you here once a decision is made — typically within 3-5 business days.</p>
                    </div>
                <?php } ?>

            </div>
        <?php } ?>
    </div>
<?php } ?>

<?php include "includes/footer.php"; ?>