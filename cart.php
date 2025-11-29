<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update' && isset($_POST['product_id'], $_POST['qty'])) {
        $productId = (int)$_POST['product_id'];
        $qty = (int)$_POST['qty'];

        if ($qty <= 0) {
            // if 0 or neg, remove
            unset($_SESSION['cart'][$productId]);
            $_SESSION['flash'] = "Removed item from your cart.";
        } else {
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]['qty'] = $qty;
                $_SESSION['flash'] = "Updated quantity for {$_SESSION['cart'][$productId]['name']}.";
            }
        }

    } elseif ($action === 'remove' && isset($_POST['product_id'])) {
        $productId = (int)$_POST['product_id'];
        if (isset($_SESSION['cart'][$productId])) {
            $name = $_SESSION['cart'][$productId]['name'];
            unset($_SESSION['cart'][$productId]);
            $_SESSION['flash'] = "Removed {$name} from your cart.";
        }

    } elseif ($action === 'clear') {
        unset($_SESSION['cart']);
        $_SESSION['flash'] = "Cleared your cart.";
    }

    // redirect after action
    header("Location: index.php?page=cart");
    exit;
}

// read the cart
$cart  = $_SESSION['cart'] ?? [];
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// get totals
$cartTotal = 0.0;
foreach ($cart as $item) {
    $cartTotal += (float)$item['price'] * (int)$item['qty'];
}

$taxRate = 0.08;
$taxAmount = round($cartTotal * $taxRate, 2);
$grandTotal = $cartTotal + $taxAmount;

// store as cookies
setCookie('cart_subtotal', $cartTotal, time() + (86400 * 7), "/");
setCookie('cart_tax', $taxAmount, time() + (86400 * 7), "/");
setCookie('cart_total', $grandTotal, time() + (86400 * 7), "/");

?>

<main class="page cart-page">
    <div class="container cart-container">
        <h1>Your Cart</h1>

        <?php if ($flash): ?>
            <div class="flash-message">
                <p><?= htmlspecialchars($flash) ?></p>
            </div>
        <?php endif; ?>

        <?php if (empty($cart)): ?>
            <div class="cart-empty">
                <p>Your cart is empty.</p>
                <a href="index.php?page=products" class="btn primary full">
                    Browse Products
                </a>
            </div>
        <?php else: ?>

            <div class="cart-actions-top">
                <form method="post" action="index.php?page=cart">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn secondary">
                        Clear Cart
                    </button>
                </form>
            </div>

            <div class="cart-table-wrapper">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $item): ?>
                            <?php
                                $id       = (int)$item['id'];
                                $name     = $item['name'];
                                $price    = (float)$item['price'];
                                $qty      = (int)$item['qty'];
                                $imgSmall = $item['image_small'] ?? null;
                                $subtotal = $price * $qty;
                            ?>
                            <tr class="cart-row">
                                <td class="cart-item">
                                    <div class="cart-item-info">
                                        <?php if (!empty($imgSmall)): ?>
                                            <img
                                                src="<?= htmlspecialchars($imgSmall) ?>"
                                                alt="<?= htmlspecialchars($name) ?>"
                                                class="cart-thumb"
                                            />
                                        <?php endif; ?>
                                        <div>
                                            <p class="cart-item-name">
                                                <?= htmlspecialchars($name) ?>
                                            </p>
                                            <p class="cart-item-link">
                                                <a href="index.php?page=product&id=<?= $id ?>">
                                                    View details
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="cart-price">
                                    $<?= number_format($price, 2) ?>
                                </td>

                                <td class="cart-qty">
                                    <form method="post" action="index.php?page=cart" class="cart-qty-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= $id ?>">
                                        <input
                                            type="number"
                                            name="qty"
                                            min="0"
                                            value="<?= $qty ?>"
                                            class="qty-input"
                                        />
                                        <button type="submit" class="btn small">
                                            Update
                                        </button>
                                    </form>
                                </td>

                                <td class="cart-subtotal">
                                    $<?= number_format($subtotal, 2) ?>
                                </td>

                                <td class="cart-remove">
                                    <form method="post" action="index.php?page=cart">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?= $id ?>">
                                        <button type="submit" class="btn link-btn">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="cart-total-label">
                                Subtotal:
                            </td>
                            <td class="cart-total-value">
                                $<?= number_format($cartTotal, 2) ?>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="cart-total-label">
                                Tax (<?=(int)($taxRate * 100) ?>%):
                            </td>
                            <td class="cart-total-value">
                                $<?= number_format($taxAmount, 2) ?>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="cart-total-label">
                                Total:
                            </td>
                            <td class="cart-total-value">
                                &dollar;<?= number_format($grandTotal, 2) ?>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="cart-footer-actions">
                <a href="index.php?page=products" class="btn secondary shopping">
                    Continue Shopping
                </a>

                <a href="index.php?page=checkout" class="btn primary checkout">Proceed to Checkout</a>
            </div>

        <?php endif; ?>
    </div>
</main>
