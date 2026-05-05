<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit;
}

// Determine mode: add or edit
$isEdit = isset($_GET["id"]);
$pet = null;

if ($isEdit) {
    $id = $_GET["id"];
    $pet = $conn->query("SELECT * FROM pets WHERE pet_id = $id")->fetch_assoc();

    if (!$pet) {
        header("Location: admin.php");
        exit;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST["name"]);
    $species = $conn->real_escape_string($_POST["species"]);
    $breed = $conn->real_escape_string($_POST["breed"]);
    $age = (int)$_POST["age"];  // numeric — cast to int instead
    $gender = $conn->real_escape_string($_POST["gender"]);
    $size = $conn->real_escape_string($_POST["size"]);
    $color = $conn->real_escape_string($_POST["color"]);
    $description = $conn->real_escape_string($_POST["description"]);
    $personality = $conn->real_escape_string($_POST["personality"]);
    $health = $conn->real_escape_string($_POST["health"]);
    $shelter = $conn->real_escape_string($_POST["shelter"]);
    $status = $conn->real_escape_string($_POST["status"]);
    if ($isEdit) {
        $image = $pet["image"];
    } else {
        $image = "";
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $tmp = $_FILES["image"]["tmp_name"];
            $originalName = $_FILES["image"]["name"];
            $newName = time() . "_" . $originalName;
            $destination = "images/" . $newName;
            if (move_uploaded_file($tmp, $destination)) {
                $image = $newName;
            }
        }
    }
    // Handle GIF upload (optional, only when adding new pet)
    if ($isEdit) {
        $gif = $pet["gif"];
    } else {
        $gif = "";
        if (isset($_FILES["gif"]) && $_FILES["gif"]["error"] == 0) {
            $tmp = $_FILES["gif"]["tmp_name"];
            $originalName = $_FILES["gif"]["name"];
            $newName = time() . "_gif_" . $originalName;
            $destination = "images/" . $newName;
            if (move_uploaded_file($tmp, $destination)) {
                $gif = $newName;
            }
        }
    }

    if ($isEdit) {
        // UPDATE existing pet
        $conn->query("UPDATE pets SET
            name = '$name',
            species = '$species',
            breed = '$breed',
            age = $age,
            gender = '$gender',
            size = '$size',
            color = '$color',
            description = '$description',
            personality = '$personality',
            health = '$health',
            shelter = '$shelter',
            image = '$image',
            status = '$status',
            gif = '$gif',
            WHERE pet_id = $id");
    } else {
        $conn->query("INSERT INTO pets (name, species, breed, age, gender, size, color, description, personality, health, shelter, image, gif, status)
                      VALUES ('$name', '$species', '$breed', $age, '$gender', '$size', '$color', '$description', '$personality', '$health', '$shelter', '$image', '$gif', '$status')");
    }

    header("Location: admin.php");
    exit;
}
?>

<?php include "includes/header.php"; ?>

<p style="margin-top: 20px;"><a href="admin.php">← Back to Admin Panel</a></p>

<div class="form-box wide">
    <h2><?php echo $isEdit ? "Edit Pet" : "Add New Pet"; ?></h2>
    <p style="text-align: center; color: #666; margin-bottom: 20px;">
        <?php echo $isEdit ? "Update the details for " . $pet["name"] . "." : "Fill in the details to add a new pet."; ?>
    </p>

    <form method="POST" action="pet-form.php<?php echo $isEdit ? '?id=' . $id : ''; ?>" enctype="multipart/form-data">

        <div class="form-row">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" required value="<?php echo $isEdit ? $pet["name"] : ''; ?>">
            </div>
            <div class="form-group">
                <label>Species *</label>
                <select name="species" required>
                    <?php
                    $speciesList = ["Dog", "Cat", "Rabbit", "Bird", "Other"];
                    foreach ($speciesList as $sp) {
                        $selected = ($isEdit && $pet["species"] == $sp) ? "selected" : "";
                        echo "<option $selected>$sp</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Breed</label>
                <input type="text" name="breed" value="<?php echo $isEdit ? $pet["breed"] : ''; ?>">
            </div>
            <div class="form-group">
                <label>Age (years) *</label>
                <input type="number" name="age" min="0" required value="<?php echo $isEdit ? $pet["age"] : ''; ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Gender *</label>
                <select name="gender" required>
                    <?php foreach (["Male", "Female"] as $g) {
                        $selected = ($isEdit && $pet["gender"] == $g) ? "selected" : "";
                        echo "<option $selected>$g</option>";
                    } ?>
                </select>
            </div>
            <div class="form-group">
                <label>Size</label>
                <select name="size">
                    <?php foreach (["Small", "Medium", "Large"] as $s) {
                        $selected = ($isEdit && $pet["size"] == $s) ? "selected" : "";
                        echo "<option $selected>$s</option>";
                    } ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Color</label>
                <input type="text" name="color" value="<?php echo $isEdit ? $pet["color"] : ''; ?>">
            </div>
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <?php foreach (["Available", "Adopted"] as $st) {
                        $selected = ($isEdit && $pet["status"] == $st) ? "selected" : "";
                        echo "<option $selected>$st</option>";
                    } ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description"><?php echo $isEdit ? $pet["description"] : ''; ?></textarea>
        </div>

        <div class="form-group">
            <label>Personality</label>
            <textarea name="personality"><?php echo $isEdit ? $pet["personality"] : ''; ?></textarea>
        </div>

        <div class="form-group">
            <label>Health Information</label>
            <textarea name="health"><?php echo $isEdit ? $pet["health"] : ''; ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Shelter</label>
                <input type="text" name="shelter" value="<?php echo $isEdit ? $pet["shelter"] : ''; ?>">
            </div>
            <?php if (!$isEdit) { ?>
                <!-- Image upload (already exists) -->
                <div class="form-group">
                    <label>Pet Image *</label>
                    <input type="file" name="image" accept="image/jpeg, image/png" required>
                    <small style="color: #666;">Static photo for the pet card (jpg, png).</small>
                </div>

                <!-- GIF upload (new, optional) -->
                <div class="form-group">
                    <label>Pet GIF (optional)</label>
                    <input type="file" name="gif" accept="image/gif">
                    <small style="color: #666;">Animated GIF shown on the detail page. Optional.</small>
                </div>
        <?php } else { ?>
        <!-- Edit mode: show what's already saved -->
        <div class="form-group">
            <label>Pet Image</label>
            <?php if ($pet["image"]) { ?>
                <p style="color: #666;">📷 <strong><?php echo $pet["image"]; ?></strong></p>
            <?php } else { ?>
                <p style="color: #888;">No image — emoji will be shown.</p>
            <?php } ?>
            <small style="color: #888;">Image cannot be changed. Delete and re-add the pet to change.</small>
        </div>

        <div class="form-group">
            <label>Pet GIF</label>
            <?php if ($pet["gif"]) { ?>
                <p style="color: #666;">🎬 <strong><?php echo $pet["gif"]; ?></strong></p>
            <?php } else { ?>
                <p style="color: #888;">No GIF uploaded.</p>
            <?php } ?>
            <small style="color: #888;">Cannot be changed after adding.</small>
        </div>
        <?php } ?>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="admin.php" class="btn">Cancel</a>
            <button type="submit" class="btn btn-primary"><?php echo $isEdit ? "Update Pet" : "Add Pet"; ?></button>
        </div>
    </form>
</div>

<?php include "includes/footer.php"; ?>