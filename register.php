<?php

$errors = [];

// per-field error messages (for inline display)
$firstNameError = "";
$lastNameError  = "";
$emailError     = "";
$passwordError  = "";

// preserve submitted values
$first_name = $first_name ?? '';
$last_name  = $last_name ?? '';
$email      = $email ?? '';

// check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // collect input
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = trim($_POST['password'] ?? '');

    // name validation
    // First name
    if ($first_name === '') {
        $firstNameError = "First Name is required.";
    } elseif (!preg_match("/^[A-Za-z' -]+$/", $first_name)) {
        $firstNameError = "Not a valid name";
    }

    // Last name
    if ($last_name === '') {
        $lastNameError = "Last name is required.";
    } elseif (!preg_match("/^[A-Za-z' -]+$/", $last_name)) {
        $lastNameError = "Not a valid name";
    }

    // email validation
    if ($email === '') {
        $emailError = "Email is required.";
    } elseif (!preg_match("/^[^\s@]+@[^\s@]+\.[^\s@]+$/", $email)) {
        $emailError = "Please enter a valid email address.";
    }

    // password validation
    if ($password === '') {
        $passwordError = "Password is required.";
    } else {
        $hasUpper      = preg_match('/[A-Z]/', $password);
        $hasSpecial    = preg_match('/[!@#$%^&*(),.?":{}|<>_\-+=\/\\\\\[\]]/', $password);
        $hasValidLen   = strlen($password) >= 6;
        $hasNoSpaces   = !preg_match('/\s/', $password);

        if (!$hasUpper || !$hasSpecial || !$hasValidLen || !$hasNoSpaces) {
            $passwordError = "Password must be at least 6 characters, include 1 uppercase, and 1 special character!";
        }
    }

    // insert if no field errors and no global errors
    if (
        $firstNameError === "" &&
        $lastNameError  === "" &&
        $emailError     === "" &&
        $passwordError  === "" &&
        empty($errors)
    ) {

        // see if email already exisits and insert if not
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            $emailError = "An account with that email already exists!";
        } else {
            $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, password)
            VALUES (?, ?, ?, ?)
            ");

            try {
                $stmt->execute([$first_name, $last_name, $email, $password]);

                header("Location: index.php?page=login&email=" . $email);
                exit;

            // catch the error
            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<main>
    <section class="login-page">
        <div class="login-card">
            <h1 class="login-title">Create Account</h1>
            <p class="login-subtitle">Join Bread Co. to order faster and track your info.</p>

            <?php if (!empty($errors)): ?>
                <div class="login-message error">
                    <?php foreach ($errors as $err): ?>
                        <p><?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form class="login-form" id="registerForm" method="post" action="index.php?page=register">
                <div class="field">
                    <label for="first_name">First Name</label>
                    <input
                        type="text"
                        name="first_name"
                        id="first_name"
                        required
                        value="<?= htmlspecialchars($first_name ?? '') ?>"
                    >
                    <p class="field-error" id="firstNameError">
                        <?= htmlspecialchars($firstNameError) ?>
                    </p>
                </div>

                <div class="field">
                    <label for="last_name">Last Name</label>
                    <input
                        type="text"
                        name="last_name"
                        id="last_name"
                        required
                        value="<?= htmlspecialchars($last_name ?? '') ?>"
                    >
                    <p class="field-error" id="lastNameError">
                        <?= htmlspecialchars($lastNameError) ?>
                    </p>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        required
                        value="<?= htmlspecialchars($email ?? '') ?>"
                    >
                    <p class="field-error" id="emailError">
                        <?= htmlspecialchars($emailError) ?>
                    </p>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <p class="pass-rules">Must include: 1 uppercase, 1 special character, 6 characters</p>
                    </ul>
                    <div class="password-field-container">
                        <input
                            class="password-field"
                            type="password"
                            id="password"
                            name="password"
                            required
                        >
                        <button type="button" class="toggle-visibility-btn">
                            <img class="small-icon" src="./assets/icons/showPassword.png">
                        </button>
                    </div>
                    <p class="field-error" id="passwordError">
                        <?= htmlspecialchars($passwordError) ?>
                    </p>
                </div>

                <button type="submit" class="btn primary">Register</button>
            </form>

            <p class="login-footer-text">
                Already have an account?
                <a href="index.php?page=login">Login here</a>.
            </p>
        </div>
    </section>
</main>
<script src="./js/register.js"></script>
