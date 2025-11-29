<?php
$id = $_GET['id'] ?? null;

if (!$id) {
    die("Product not found.");
}

// fetch the single product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found in database.");
}
// add to cart (or update qt)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['qty']++;
    } else {
        $_SESSION['cart'][$id] = [
            'id'          => $product['id'],
            'name'        => $product['name'],
            'price'       => $product['price'],
            'qty'         => 1,
            'image_medium' => $product['image_medium'] ?? null,
        ];
    }
    $_SESSION['flash'] = "{$product['name']} added to cart!";
    header("Location: index.php?page=product&id=".$id);
    exit;
}
?>

<div class="product-page">
    <?php if (!empty($_SESSION['flash'])) : ?>
        <div class="flash-message">
            <?= htmlspecialchars($_SESSION['flash']) ?>
        </div>
        <?php unset($_SESSION['']); ?>
    <?php endif; ?>
    <h1><?= htmlspecialchars($product['name']) ?></h1>

    <img class="product-img" src=<?= htmlspecialchars($product['image_medium']) ?> alt="">

    <p class="price">$<?= htmlspecialchars($product['price']) ?></p>

    <p class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

    <form method="post">

    <button name="add">Add to Cart</button>
    </form>
    
</div>
