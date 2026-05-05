<?php include "includes/db.php"; ?>
<?php
$id = $_GET["id"];

$result = $conn->query("SELECT * FROM pets WHERE pet_id = $id");
$pet = $result->fetch_assoc();

if (!$pet) {
    header("Location: index.php");
    exit;
}
?>
<?php include "includes/header.php"; ?>

<p style="margin-top: 20px;"><a href="index.php">← Back to all pets</a></p>

<div class="pet-detail">
    <div class="pet-detail-image">
        <?php
        // Show GIF if available, otherwise show static image, otherwise emoji
        if ($pet["gif"] && file_exists("images/" . $pet["gif"])) {
            echo '<img src="images/' . $pet["gif"] . '" alt="' . $pet["name"] . '">';
        } elseif (file_exists("images/" . $pet["image"])) {
            echo '<img src="images/' . $pet["image"] . '" alt="' . $pet["name"] . '">';
        } else {
            echo getEmoji($pet["species"]);
        }
        ?>
    </div>
    <div class="pet-detail-info">
        <h1><?php echo $pet["name"]; ?></h1>
        <div class="pet-tags">
            <span class="tag"><?php echo $pet["species"]; ?></span>
            <span class="tag"><?php echo $pet["gender"]; ?></span>
            <span class="tag"><?php echo $pet["age"]; ?> years old</span>
            <span class="tag"><?php echo $pet["size"]; ?></span>
        </div>
        <p style="margin-top: 15px;"><?php echo $pet["description"]; ?></p>

        <div class="info-section">
            <h3>Quick Facts</h3>
            <p><strong>Breed:</strong> <?php echo $pet["breed"]; ?></p>
            <p><strong>Color:</strong> <?php echo $pet["color"]; ?></p>
            <p><strong>Size:</strong> <?php echo $pet["size"]; ?></p>
        </div>

        <div class="info-section">
            <h3>Personality</h3>
            <p><?php echo $pet["personality"]; ?></p>
        </div>

        <div class="info-section">
            <h3>Health</h3>
            <p><?php echo $pet["health"]; ?></p>
        </div>

        <div class="info-section">
            <h3>Shelter</h3>
            <p>📍 <?php echo $pet["shelter"]; ?></p>
        </div>

        <div style="margin-top: 20px;">
            <a href="adopt.php?id=<?php echo $pet["pet_id"]; ?>" class="btn btn-primary">Adopt <?php echo $pet["name"]; ?></a>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>