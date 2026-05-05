<?php
session_start();
include "includes/db.php";

// Require admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit;
}

// Handle approve/reject actions from URL
// e.g. admin.php?action=approve&app_id=5
if (isset($_GET["action"]) && isset($_GET["app_id"])) {
    $app_id = $_GET["app_id"];
    $action = $_GET["action"];

    if ($action == "approve") {
        $conn->query("UPDATE applications SET status = 'Approved' WHERE app_id = $app_id");
    } elseif ($action == "reject") {
        $conn->query("UPDATE applications SET status = 'Rejected' WHERE app_id = $app_id");
    } elseif ($action == "delete-pet") {
        $pet_id = $_GET["app_id"]; // reusing the param
        $conn->query("DELETE FROM pets WHERE pet_id = $pet_id");
    }

    // Redirect to clean URL (avoids re-running on refresh)
    header("Location: admin.php");
    exit;
}

// Get counts for stats
$totalPets = $conn->query("SELECT COUNT(*) AS c FROM pets")->fetch_assoc()["c"];
$availablePets = $conn->query("SELECT COUNT(*) AS c FROM pets WHERE status = 'Available'")->fetch_assoc()["c"];
$pendingApps = $conn->query("SELECT COUNT(*) AS c FROM applications WHERE status = 'Pending'")->fetch_assoc()["c"];
$totalApps = $conn->query("SELECT COUNT(*) AS c FROM applications")->fetch_assoc()["c"];

// Get all applications with pet info
$applications = $conn->query("SELECT applications.*, pets.name AS pet_name, pets.species
                              FROM applications
                              JOIN pets ON applications.pet_id = pets.pet_id
                              ORDER BY
                                CASE applications.status
                                    WHEN 'Pending' THEN 1
                                    WHEN 'Approved' THEN 2
                                    ELSE 3
                                END,
                                applications.submitted_at DESC");

// Get all pets
$pets = $conn->query("SELECT * FROM pets ORDER BY pet_id DESC");
?>
<?php include "includes/header.php"; ?>

<h1 class="page-title">Admin Panel ⚙️</h1>

<div class="alert alert-info">
    Welcome back, <strong><?php echo $_SESSION["full_name"]; ?></strong>!
    <?php if ($pendingApps > 0) { ?>
        You have <strong><?php echo $pendingApps; ?> pending application<?php echo $pendingApps > 1 ? 's' : ''; ?></strong> to review.
    <?php } ?>
</div>

<!-- ================== STATS ================== -->
<div class="stat-grid">
    <div class="stat-card">
        <h3>Total Pets</h3>
        <div class="stat-value"><?php echo $totalPets; ?></div>
    </div>
    <div class="stat-card available">
        <h3>Available</h3>
        <div class="stat-value"><?php echo $availablePets; ?></div>
    </div>
    <div class="stat-card pending">
        <h3>Pending Apps</h3>
        <div class="stat-value"><?php echo $pendingApps; ?></div>
    </div>
    <div class="stat-card total">
        <h3>Total Apps</h3>
        <div class="stat-value"><?php echo $totalApps; ?></div>
    </div>
</div>

<!-- ================== APPLICATIONS ================== -->
<h2 class="page-title">Adoption Applications</h2>

<?php if ($applications->num_rows == 0) { ?>
    <div class="alert alert-info">No applications submitted yet.</div>
<?php } else { ?>
    <table>
        <thead>
            <tr>
                <th>App ID</th>
                <th>Applicant</th>
                <th>Pet</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($app = $applications->fetch_assoc()) { ?>
                <tr>
                    <td><strong>PAW-<?php echo str_pad($app["app_id"], 5, "0", STR_PAD_LEFT); ?></strong></td>
                    <td>
                        <?php echo $app["applicant_name"]; ?><br>
                        <small style="color: #888;"><?php echo $app["applicant_email"]; ?></small>
                    </td>
                    <td><?php echo $app["pet_name"]; ?> (<?php echo $app["species"]; ?>)</td>
                    <td><?php echo date("M j, Y", strtotime($app["submitted_at"])); ?></td>
                    <td>
                        <?php if ($app["status"] == "Approved") { ?>
                            <span class="badge badge-approved">Approved</span>
                        <?php } elseif ($app["status"] == "Rejected") { ?>
                            <span class="badge badge-rejected">Rejected</span>
                        <?php } else { ?>
                            <span class="badge badge-pending">Pending</span>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($app["status"] == "Pending") { ?>
                            <a href="admin.php?action=approve&app_id=<?php echo $app["app_id"]; ?>"
                               class="btn btn-success"
                               style="padding: 4px 10px; font-size: 12px;"
                               onclick="return confirm('Approve this application?');">Approve</a>
                            <a href="admin.php?action=reject&app_id=<?php echo $app["app_id"]; ?>"
                               class="btn btn-danger"
                               style="padding: 4px 10px; font-size: 12px;"
                               onclick="return confirm('Reject this application?');">Reject</a>
                        <?php } else { ?>
                            <span style="color: #888;">—</span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } ?>

<!-- ================== MANAGE PETS ================== -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 40px;">
    <h2 class="page-title" style="margin: 0;">Manage Pets</h2>
    <a href="pet-form.php" class="btn btn-primary">+ Add New Pet</a>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Species</th>
            <th>Breed</th>
            <th>Age</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($pet = $pets->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $pet["pet_id"]; ?></td>
                <td><strong><?php echo $pet["name"]; ?></strong></td>
                <td><?php echo $pet["species"]; ?></td>
                <td><?php echo $pet["breed"]; ?></td>
                <td><?php echo $pet["age"]; ?> yrs</td>
                <td>
                    <?php if ($pet["status"] == "Available") { ?>
                        <span class="badge badge-approved">Available</span>
                    <?php } else { ?>
                        <span class="badge badge-rejected">Adopted</span>
                    <?php } ?>
                </td>
                <td>
                    <a href="pet-form.php?id=<?php echo $pet["pet_id"]; ?>" class="btn" style="padding: 4px 10px; font-size: 12px;">Edit</a>
                    <a href="admin.php?action=delete-pet&app_id=<?php echo $pet["pet_id"]; ?>"
                       class="btn btn-danger"
                       style="padding: 4px 10px; font-size: 12px;"
                       onclick="return confirm('Delete <?php echo $pet["name"]; ?>? This cannot be undone.');">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php include "includes/footer.php"; ?>