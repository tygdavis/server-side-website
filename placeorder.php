<?php
$orderId = $_GET["order_id"] ?? null;
$deliveryType = $_GET["delivery_type"] ?? null;
?>
<main class="thankyou-wrapper">
    <div class="thankyou-card">

        <div class="checkmark">
            <span>✓</span>
        </div>

        <h1>Thank You!</h1>
        <p class="subtitle">Your order has been successfully placed.</p>

        <div class="order-details">
            <p><strong>Order Number:</strong> #<?= htmlspecialchars($orderId) ?></p>

            <?php if ($deliveryType === "delivery"): ?>
                <p class="message">Your order will be delivered soon!</p>
            <?php else: ?>
                <p class="message">We look forward to seeing you soon!</p>
            <?php endif; ?>
        </div>

        <a href="index.php?page=home" class="home-btn">Return Home</a>
    </div>
</main>
