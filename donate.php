<?php include "includes/db.php"; ?>
<?php
$submitted = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $amount = $_POST["amount"];
    $recipient = $_POST["recipient"];
    $payment = $_POST["payment"];
    $submitted = true;
}
?>
<?php include "includes/header.php"; ?>

<h1 class="page-title">Make a Donation 💛</h1>

<div class="form-box wide">
    <?php if ($submitted) { ?>
        <!-- Thank you screen -->
        <div class="alert alert-success">
            <h2 style="text-align: center; color: #155724;">✓ Thank You, <?php echo $name; ?>!</h2>
            <p style="text-align: center; margin-top: 10px;">
                Your donation of <strong>Rs. <?php echo number_format($amount); ?></strong>
                to <strong><?php echo $recipient; ?></strong> via <strong><?php echo $payment; ?></strong> has been received.<br><br>
                Your contribution makes a real difference for pets waiting for a home.
            </p>
            <p style="text-align: center; margin-top: 20px;">
                <a href="index.php" class="btn btn-primary">Back to Home</a>
            </p>
        </div>

    <?php } else { ?>

        <p style="text-align: center; color: #666; margin-bottom: 20px;">
            Help us care for animals in need. Every contribution counts.
        </p>

        <form method="POST" action="donate.php">

            <div class="form-row">
                <div class="form-group">
                    <label>Your Name *</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
            </div>

            <div class="form-group">
                <label>Donate to *</label>
                <select name="recipient" required>
                    <option value="PawHub General Fund">PawHub General Fund (distributed across all shelters)</option>
                    <option value="Kathmandu Animal Shelter">Kathmandu Animal Shelter</option>
                    <option value="Happy Paws Rescue">Happy Paws Rescue</option>
                    <option value="Srijana Animal Shelter">Srijana Animal Shelter</option>
                </select>
            </div>

            <div class="form-group">
                <label>Amount (Rs.) *</label>
                <input type="number" name="amount" min="100" placeholder="Enter amount" required>
                <small style="color: #888;">Suggested: Rs. 500 · 1,000 · 2,500 · 5,000</small>
            </div>

            <div class="form-group">
                <label>Payment Method *</label>
                <select name="payment" required>
                    <option value="">-- Select --</option>
                    <option>Khalti</option>
                    <option>eSewa</option>
                    <option>Credit / Debit Card</option>
                </select>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">💛 Donate Now</button>
            </div>
        </form>
    <?php } ?>
</div>

<?php include "includes/footer.php"; ?>