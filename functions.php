<?php
session_start();

function pdo_connect_mysql() {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'shop';

    try {
        return new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    } catch (PDOException $e) {
        exit("Database error.");
    }
}

function template_header($page) {
    $globalCss = "./styles/style.css";
    $pageCss   = "./styles/$page.css";
    ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= ucfirst($page) ?></title>

    <link rel="stylesheet" href="<?= $globalCss ?>">

    <?php if (file_exists($pageCss)): ?>
        <link rel="stylesheet" href="<?= $pageCss ?>">
    <?php endif; ?>

    <?php if ($page === 'register' || $page === 'login'): ?>
        <link rel="stylesheet" href="styles/login.css">
    <?php endif; ?>
</head>
<body class="layout">

<header class="site-header">
    <div class="nav-container">
        <a href="index.php?page=home" class="logo">Bread Co.</a>

        <button class="nav-toggle">
            <img class="icon darken" src="assets/icons/openMenu.png" alt="open"/>
        </button>

        <nav class="nav-links">
            <a href="index.php?page=home">Home</a>
            <a href="index.php?page=products">Products</a>
            <a href="index.php?page=cart">Cart</a>
            <a href="index.php?page=contact">Contact</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="index.php?page=account">Account</a>
                <a href="index.php?page=logout">Logout</a>
            <?php else: ?>
                <a href="index.php?page=login">Login</a>
                <a href="index.php?page=register">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="page">
    <?php } ?>

    <?php
    function template_footer() {
    ?>
</main>

<footer>
    <p>&copy; <?= date("Y") ?> Bread Co</p>
</footer>

<script src="./js/header.js"></script>
<script src="./js/passwordVisibility.js"></script>
</body>
</html>
<?php } ?>
