<?php include "includes/db.php"; ?>
<?php
$submitted = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submitted = true;
}
?>
<?php include "includes/header.php"; ?>

<h1 class="page-title">Contact Us 📬   </h1>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; margin-top: 20px;">

    <!-- Left: Contact info -->
    <div class="form-box" style="margin: 0; max-width: none;">
        <h2 style="text-align: left;">Get in Touch</h2>
        <p style="color: #666; margin-bottom: 20px;">
            Have questions about adoption, want to volunteer, or just want to say hi?
            We'd love to hear from you.
        </p>

        <div style="margin-bottom: 15px;">
            <strong style="color: #e85d2a;">📍 Address</strong><br>
            <span style="color: #666;">Kapan, Kathmandu, Nepal</span>
        </div>

        <div style="margin-bottom: 15px;">
            <strong style="color: #e85d2a;">📞 Phone</strong><br>
            <span style="color: #666;">+977 9800000000</span>
        </div>

        <div style="margin-bottom: 15px;">
            <strong style="color: #e85d2a;">✉️ Email</strong><br>
            <span style="color: #666;">info@pawhub.com</span>
        </div>

        <div style="margin-bottom: 15px;">
            <strong style="color: #e85d2a;">🕐 Hours</strong><br>
            <span style="color: #666;">Sun–Fri: 9 AM – 6 PM<br>Saturday: Closed</span>
        </div>
    </div>

    <!-- Right: Contact form -->
    <div class="form-box wide" style="margin: 0;">
        <?php if ($submitted) { ?>
            <div class="alert alert-success">
                <h2 style="text-align: center; color: #155724;">✓ Message Sent!</h2>
                <p style="text-align: center; margin-top: 10px;">
                    Thanks for reaching out! We'll get back to you within 24 hours.
                </p>
                <p style="text-align: center; margin-top: 20px;">
                    <a href="index.php" class="btn btn-primary">Back to Home</a>
                </p>
            </div>
        <?php } else { ?>
            <h2>Send Us a Message</h2>
            <p style="text-align: center; color: #666; margin-bottom: 20px;">
                Fill in the form and we'll respond as soon as we can.
            </p>

            <form method="POST" action="contact.php">
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
                    <label>Subject</label>
                    <input type="text" name="subject" placeholder="What's this about?">
                </div>

                <div class="form-group">
                    <label>Message *</label>
                    <textarea name="message" required style="min-height: 150px;" placeholder="Type your message here..."></textarea>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </div>
            </form>
        <?php } ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>