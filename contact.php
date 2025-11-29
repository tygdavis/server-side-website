<?php


$errors = [];
$success = false;

$session_fname = "";
$session_lname = "";
$session_email = "";

if (isset($_SESSION["user_fname"]) 
    && isset( $_SESSION["user_lname"] ) && isset( $_SESSION["user_email"] )) {
$session_fname = $_SESSION["user_fname"];
$session_lname = $_SESSION["user_lname"];
$session_email = $_SESSION["user_email"];
}

// form inputs
$userId = $_SESSION["user_id"] ?? null;
$first   = '';
$last    = '';
$email   = '';
$phone   = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // trim all inputs
    $first   = trim($_POST['first'] ?? '');
    $last    = trim($_POST['last'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // validate form
    if ($first === '') {
        $errors['first'] = 'First name is required.';
    }

    if ($last === '') {
        $errors['last'] = 'Last name is required.';
    }

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($message === '') {
        $errors['message'] = 'Please enter a message.';
    }

    // send msg to db
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO contact_messages (user_id, first_name, last_name, email, phone, message)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $first,
            $last,
            $email,
            $phone !== '' ? $phone : null,
            $message,
        ]);

        $success = true;

        // clear the form fields
        $first = $last = $email = $phone = $message = '';
        // redirect
        header("Location: index.php?page=contact&step=msg_sent");
        exit;
    }
}
?>

<section class="contact">
    <div class="contact-container">

        <?php if ($success): ?>
            <div class="alert alert-success">
                Thanks for reaching out! We’ve received your message and will reply within one business day.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <p>Please fix the following issues:</p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="contact-grid">

            <aside class="contact-intro">
                <h1 class="contact-title">have a<br />question?</h1>

                <p class="intro">
                    At <em>Bread Co.</em>, we’re here to help! Fill out the form or
                    reach us via email or phone. Our team replies within one
                    business day (M–F, 9am–5pm).
                </p>

                <ul class="info-list">
                    <li>
                        <strong>Email:</strong>
                        <a href="mailto:info@breadco.com">info@breadco.com</a>
                    </li>
                    <li>
                        <strong>Phone:</strong>
                        <a href="tel:+1123456789">+1 (123) 456-789</a>
                    </li>
                    <li><strong>Chat:</strong> Connect with us</li>
                </ul>
            </aside>

            <form
                id="contact-form"
                class="contact-form"
                method="post"
                action="index.php?page=contact"
            >
                <div class="form-row two">
                    <div class="field">
                        <label for="first">First Name *</label>
                        <input
                            id="first"
                            name="first"
                            type="text"
                            required
                            value="<?= $session_fname ? htmlspecialchars($session_fname) : $first?>"
                        />
                        <?php if (isset($errors['first'])): ?>
                            <small class="field-error"><?= htmlspecialchars($errors['first']) ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="last">Last Name *</label>
                        <input
                            id="last"
                            name="last"
                            type="text"
                            required
                            value="<?= $session_lname ? htmlspecialchars($session_lname) : $last ?>"
                        />
                        <?php if (isset($errors['last'])): ?>
                            <small class="field-error"><?= htmlspecialchars($errors['last']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="field">
                    <label for="email">Email *</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        value="<?= $session_email ? htmlspecialchars($session_email) : $email ?>"
                    />
                    <?php if (isset($errors['email'])): ?>
                        <small class="field-error"><?= htmlspecialchars($errors['email']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-row two">
                    <div class="field">
                        <label for="phone">Phone Number (optional)</label>
                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            value="<?= htmlspecialchars($phone) ?>"
                        />
                    </div>
                </div>

                <div class="field">
                    <label for="message">Message *</label>
                    <textarea
                        id="message"
                        name="message"
                        rows="6"
                        required
                    ><?= htmlspecialchars($message) ?></textarea>
                    <?php if (isset($errors['message'])): ?>
                        <small class="field-error"><?= htmlspecialchars($errors['message']) ?></small>
                    <?php endif; ?>
                </div>

                <button type="submit" class="submit-bar primary">Submit</button>
            </form>

        </div>

        <div class="map-row">
            <h3>Directions</h3>
            <iframe
                title="Map showing Truman State University"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3048.1640293353253!2d-92.58096209999995!3d40.183162900000006!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x87e80e6229f24d3b%3A0x6a371b8f8691c7d6!2sTruman%20State%20University!5e0!3m2!1sen!2sus!4v1761754323288!5m2!1sen!2sus"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen>
            </iframe>
        </div>
    </div>
</section>

<script src="./js/contact.js"></script>
