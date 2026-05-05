<?php include "includes/db.php"; ?>
<?php
// Read filters from the URL (with defaults if empty)
$search = isset($_GET["search"]) ? $conn->real_escape_string($_GET["search"]) : "";
$species = isset($_GET["species"]) ? $conn->real_escape_string($_GET["species"]) : "";
$gender = isset($_GET["gender"]) ? $conn->real_escape_string($_GET["gender"]) : "";

// Build the SQL query piece by piece
$sql = "SELECT * FROM pets WHERE status = 'Available'";

if ($search != "") {
    $sql .= " AND (name LIKE '%$search%' OR breed LIKE '%$search%')";
}
if ($species != "") {
    $sql .= " AND species = '$species'";
}
if ($gender != "") {
    $sql .= " AND gender = '$gender'";
}

$sql .= " ORDER BY pet_id DESC";

$result = $conn->query($sql);
?>
<?php include "includes/header.php"; ?>

<!-- ================== FULL-VIEWPORT HERO ================== -->
<section class="hero-full">
    <div class="hero-full-inner">
        <h1>Find your forever friend</h1>
        <p>Browse adoptable pets from trusted shelters across Nepal</p>

        <div class="hero-buttons">
            <a href="#pets" class="btn btn-primary">🐾 Adopt a Pet</a>
            <a href="donate.php" class="btn btn-donate">💛 Donate</a>
        </div>

        <div class="hero-stats">
            <div><strong>200+</strong><span>Pets Adopted</span></div>
            <div><strong>15</strong><span>Partner Shelters</span></div>
            <div><strong>500+</strong><span>Happy Families</span></div>
            <div><strong>24/7</strong><span>Support</span></div>
        </div>
    </div>

    <a href="#pets" class="hero-scroll-cue" aria-label="Scroll to pet listings">
        <span>Scroll to browse</span>
        <span class="hero-scroll-arrow">&#8595;</span>
    </a>
</section>

<!-- ================== SEARCH ================== -->
<form id="search" class="filter-bar" method="GET" action="index.php#pets">
    <div class="filter-group">
        <label>Search</label>
        <input type="text" name="search" placeholder="Name or breed..." value="<?php echo $search; ?>">
    </div>

    <div class="filter-group">
        <label>Species</label>
        <select name="species">
            <option value="">All</option>
            <?php
            $speciesList = ["Dog", "Cat", "Rabbit", "Bird", "Other"];
            foreach ($speciesList as $sp) {
                $selected = ($species == $sp) ? "selected" : "";
                echo "<option value=\"$sp\" $selected>$sp</option>";
            }
            ?>
        </select>
    </div>

    <div class="filter-group">
        <label>Gender</label>
        <select name="gender">
            <option value="">Any</option>
            <option value="Male" <?php if ($gender == "Male") echo "selected"; ?>>Male</option>
            <option value="Female" <?php if ($gender == "Female") echo "selected"; ?>>Female</option>
        </select>
    </div>

    <div class="filter-group">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary">Search</button>
    </div>

    <div class="filter-group">
        <label>&nbsp;</label>
        <a href="index.php" class="btn">Reset</a>
    </div>
</form>

<!-- ================== PET LISTING ================== -->
<div style="display: flex; justify-content: space-between; align-items: baseline; margin: 20px 0;">
    <h2 id="pets" class="page-title" style="margin: 0;">Available Pets</h2>
    <span style="color: #888;">
        <?php
        if ($search != "" || $species != "" || $gender != "") {
            echo "Found " . $result->num_rows . " match" . ($result->num_rows == 1 ? '' : 'es');
        } else {
            echo "Showing " . $result->num_rows . " pet" . ($result->num_rows == 1 ? '' : 's');
        }
        ?>
    </span>
</div>

<?php if ($result->num_rows == 0) { ?>
    <div class="alert alert-info">
        No pets match your filters. Try adjusting your search or <a href="index.php">view all pets</a>.
    </div>
<?php } else { ?>
    <div class="pet-grid">
        <?php while ($pet = $result->fetch_assoc()) { ?>
            <div class="pet-card">
                <div class="pet-image">
                    <?php
                    if (file_exists("images/" . $pet["image"])) {
                        echo '<img src="images/' . $pet["image"] . '" alt="' . $pet["name"] . '">';
                    } else {
                        echo getEmoji($pet["species"]);
                    }
                    ?>
                </div>
                <div class="pet-info">
                    <h3><?php echo $pet["name"]; ?></h3>
                    <div class="pet-tags">
                        <span class="tag"><?php echo $pet["species"]; ?></span>
                        <span class="tag"><?php echo $pet["gender"]; ?></span>
                        <span class="tag"><?php echo $pet["age"]; ?> yrs</span>
                    </div>
                    <p><?php echo $pet["breed"]; ?></p>
                    <br>
                    <a href="pet-detail.php?id=<?php echo $pet["pet_id"]; ?>" class="btn btn-primary">View Details</a>
                </div>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<?php include "includes/footer.php"; ?>