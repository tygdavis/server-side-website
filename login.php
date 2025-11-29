<?php
// error message
$error = "";

$isReturningVisitor = false;
// check if user has visited before
if (isset($_COOKIE['visited_before'])) {
    $isReturningVisitor = true;
} else {
    // create cookie for a year
    setcookie('visited_before', 'yes', time() + 60*60*24*365, '/');
}

$emailError    = "";
$passwordError = "";

$lastEmail = "";

// check if form is submitted
if ($_SERVER['REQUEST_METHOD']==="POST" && isset($_POST['login'])) {

    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $lastEmail = $email;

    // email validation
    if ($email === '') {
        $emailError = "Please enter a valid email address.";
    } elseif (!preg_match("/^[^\s@]+@[^\s@]+\.[^\s@]+$/", $email)) {
        $emailError = "Please enter a valid email address.";
    }

    // password validation
    if ($password === '') {
        $passwordError = "Password is required.";
    } elseif (preg_match('/\s/', $password)) {
        $passwordError = "Password cannot contain spaces.";
    }

    // only try to log in if there are no validation errors
    if ($emailError === "" && $passwordError === "") {

        // preload user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // check credentials
        if ($user && $user['password'] === $password) {

            // save session info
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_fname'] = $user['first_name'];
            $_SESSION['user_lname'] = $user['last_name'];
            $_SESSION['user_email'] = $user['email'];

            // try to update messages after a successful login
            $stmt = $pdo->prepare("
                UPDATE contact_messages
                SET user_id = ? 
                WHERE user_id IS NULL
                AND email = ? 
            ");
            $stmt->execute([$_SESSION["user_id"], $_SESSION["user_email"]]);

            // redirect to account page
            header("Location: index.php?page=account");
            exit;

        } else {
            // only show this if validation passed but credentials are wrong
            $error = "Invalid email or password.";
        }
    }
}
?>

<main>
    <section class="login-page">
        <div class="login-card">
            <h1 class="login-title">
                <?= $isReturningVisitor ? "Welcome back" : "Welcome" ?>
            </h1>
            <p class="login-subtitle">Sign in to continue</p>

            <?php if (!empty($error)): ?>
                <p class="login-message error"><?= htmlspecialchars($error) ?></p>
            <?php endif;?>

            <form class="login-form" id="loginForm" method="post" action="index.php?page=login">
                <div class="field">
                    <label for="email">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="<?= htmlspecialchars($lastEmail) ?>"
                        required
                    >
                    <p class="field-error" id="emailError">
                        <?= htmlspecialchars($emailError) ?>
                    </p>
                </div>

                <div class="field">
                    <label for="password">Password</label>
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

                <button type="submit" name="login" class="btn primary">Log In</button>
            </form>

            <p class="login-footer-text">
                Don’t have an account?
                <a href="index.php?page=register">Sign up</a>
            </p>
        </div>
    </section>
</main>
<script src="./js/login.js"></script>
