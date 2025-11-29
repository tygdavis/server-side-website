<?php
// must be logged in to view this
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}

$userId = $_SESSION['user_id'];

$emailError = "";
$emailSuccess = "";
$passError = "";
$passSuccess = "";

// try to update email on submit
if ($_SERVER['REQUEST_METHOD']==="POST" && isset($_POST['update_email'])) {
    $newEmail = trim($_POST['new_email'] ?? '');
    
    if ($newEmail === '') {
        $emailError = "Email cannot be empty.";
    } elseif (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $newEmail)) {
        $emailError = "Invalid email format.";
    } else {
        // try to update
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        try {
            $stmt->execute([$newEmail, $userId]);

            // update session value
            $_SESSION['user_email'] = $newEmail;

            $emailSuccess = "Email updated successfully";
        } catch (PDOException $e) {
            // 1062 = duplicate entry
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
                $emailError = "That email is already registered.";
            } else {
                $emailError = "Database error: " . $e->getMessage();
            }
        }
    }
}

// try to update password on submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $newPassword     = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['password_confirm'] ?? '');
    $jsError         = trim($_POST['js_error'] ?? ''); // optional error sent from JS

    // password checks
    if ($newPassword === '' || $confirmPassword === '') {
        $passError = "Both password fields are required.";
    } elseif ($newPassword !== $confirmPassword) {
        $passError = "Passwords do not match.";
    } else {
        // must be at least 6 characters
        // must have 1 uppercase letter
        // must have one special character
        $hasUpper    = preg_match('/[A-Z]/', $newPassword);
        $hasSpecial  = preg_match('/[!@#$%^&*(),.?":{}|<>_\-+=\/\\\\\[\]]/', $newPassword);
        $hasValidLen = strlen($newPassword) >= 6;
        $hasNoSpaces = !preg_match('/\s/', $newPassword);

        if (!$hasUpper || !$hasSpecial || !$hasValidLen || !$hasNoSpaces) {
            $passError = "Password must be at least 6 characters, include 1 uppercase, and 1 special character with no spaces.";
        }
    }

    // display error. php first
    if ($passError === "" && $jsError !== "") {
        $passError = $jsError;
    }

    // if no errors, update password
    if ($passError === "") {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newPassword, $userId]);
        $passSuccess = "Password updated successfully.";
    }
}

// load user from db
$stmt = $pdo->prepare("SELECT first_name, last_name, email, password, created_at, updated_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userRow) {
    echo "<p>Could not load your account information.</p>";
    return;
}

$first_name  = $userRow['first_name'];
$last_name   = $userRow['last_name'];
$email       = $userRow['email'];
$pwd         = $userRow['password'];
$created_at  = $userRow['created_at'];
$updated_at  = $userRow['updated_at'];

// get all past contact messages
$stmt = $pdo->prepare("
    SELECT message, created_at
    FROM contact_messages
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// get all orders
$stmt = $pdo->prepare("
    SELECT id, delivery_type, address1, address2, city, zipcode,
           subtotal, tax, total, created_at, card_number
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="account-container">
    <h1>My Account</h1>
    <div class="personal-info">
        <h3>Hey <?= htmlspecialchars($first_name) ?>, here's your info.</h3>
        <p><strong>Full Name:</strong> <?= htmlspecialchars($first_name . ' ' . $last_name) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
        <p><strong>Created At:</strong> <?= date("M d, Y h:i A", strtotime($created_at)) ?></p>
        <p><strong>Last Updated:</strong> <?= date("M d, Y h:i A", strtotime($updated_at)) ?></p>
    </div>
    <hr>
    <h2>Your Past Orders</h2>
    <?php if (empty($orders)): ?>
        <p>You have no previous orders!</p>
    <?php else: ?>
        <div class="orders-container">
            <?php foreach ($orders as $order): ?>
                <?php
                    if ($order['delivery_type'] === 'pickup') {
                        $addressText = 'Pickup order';
                    } else {
                        $parts = [
                            $order['address1'],
                            $order['address2'],
                            $order['city'],
                            $order['zipcode']
                        ];
                        $addressText = implode(', ', array_filter($parts));
                    }

                    $cardDisplay = 'N/A';
                    if (!empty($order['card_number'])) {
                        $last4 = substr($order['card_number'], -4);
                        $cardDisplay = '**** **** **** ' . $last4;
                    }
                ?>
                <div class="order-card">
                    <div class="order-left">
                        <p><strong>Order #:</strong> <?= htmlspecialchars($order['id']) ?></p>
                        <p><strong>Date:</strong>
                            <?= date("M d, Y h:i A", strtotime($order['created_at'])) ?>
                        </p>
                        <p><strong>Type:</strong> <?= htmlspecialchars(ucfirst($order['delivery_type'])) ?></p>
                        <p><strong>Address / Pickup:</strong> <?= htmlspecialchars($addressText) ?></p>
                    </div>
                    <div class="order-right">
                        <p><strong>Subtotal:</strong>
                            $<?= number_format((float)$order['subtotal'], 2) ?>
                        </p>
                        <p><strong>Tax:</strong>
                            $<?= number_format((float)$order['tax'], 2) ?>
                        </p>
                        <p><strong>Total:</strong>
                            $<?= number_format((float)$order['total'], 2) ?>
                        </p>
                        <p><strong>Card:</strong> <?= htmlspecialchars($cardDisplay) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <hr>
    <h2>Your Past Contact Messages</h2>
    <?php if (empty($messages)): ?>
        <p>You haven't sent any messages yet.</p>
    <?php else: ?>
        <div class="messages-container">
            <?php foreach($messages as $m) : ?>
                <div class="message-card">
                    <p class="msg-text">
                        <?= htmlspecialchars(trim($m['message'])) ?>
                    </p>
                    <p class="msg-date">
                        Sent on <?= date("M d, Y h:i A", strtotime($m["created_at"])) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <hr>
    <div class="update-box">
        <h2>Update Email</h2>

        <?php if ($emailError): ?>
            <p style="color:red;"><?= htmlspecialchars($emailError) ?></p>
        <?php endif; ?>

        <?php if ($emailSuccess): ?>
            <p style="color:green;"><?= htmlspecialchars($emailSuccess) ?></p>
        <?php endif; ?>

        <form method="post" action="index.php?page=account">
            <label for="new_email">New Email:</label>
            <input
                type="email"
                id="new_email"
                name="new_email"
                value="<?= htmlspecialchars($email) ?>"
                required
            >
            <button type="submit" name="update_email" value="1">Update Email</button>
        </form>
    </div>

    <div class="update-box">
        <h2>Update Password</h2>
        <p>Password Must Include:</p>
        <ul class="password-rules">
            <li>6 characters</li>
            <li>1 uppercase</li>
            <li>1 special character</li>
        </ul>

        <?php if ($passError): ?>
            <p style="color:red;"><?= htmlspecialchars($passError) ?></p>
        <?php endif; ?>

        <?php if ($passSuccess): ?>
            <p style="color:green;"><?= htmlspecialchars($passSuccess) ?></p>
        <?php endif; ?>

        <form method="post" id="update-pass-form" action="index.php?page=account">
            <label for="password">New Password:</label>
            <div class="password-field-container">
                <input
                    class="password-field password"
                    type="password"
                    id="password"
                    name="password"
                    required
                >
                <button type="button" class="toggle-visibility-btn">
                    <img class="small-icon" src="./assets/icons/showPassword.png">
                </button>
            </div>

            <label for="password_confirm">Confirm New Password:</label>
            <input
                type="password"
                class="password"
                id="password_confirm"
                name="password_confirm"
                required
            >

            <input type="hidden" name="js_error" id="js_error">

            <button type="submit" name="update_password" value="1">Update Password</button>
        </form>
    </div>
</div>
