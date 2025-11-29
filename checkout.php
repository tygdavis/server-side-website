<?php 

    $deliveryErrors = [];
    $paymentErrors = [];


    $deliverySuccess = $_SESSION["checkout_checkpoints"]["delivery"] ?? false;
    $paymentSuccess = false;
    $scrollToPayment = false;
    $session_fname = "";
    $session_lname = "";
    $session_email = "";
    $existingAddress = "";
    $address1Db = "";
    $address2Db = "";
    $cityDb = "";
    $zipDb = "";

    // cart info
    $cart = $_SESSION["cart"] ?? [];
    $subTotal = 0;
    foreach($cart as $item) {
        $subTotal += $item["qty"] * $item["price"];
    }
    $taxRate = 0.08;
    $discountError = '';
    $discountAmount = 0;

    // if there's already a discount in the session, recalc it
    if (!empty($_SESSION['discount'])) {
        $d    = $_SESSION['discount'];
        $rate = (float)($d['rate'] ?? 0);
        $type = $d['type'] ?? 'percent';

        if ($type === 'percent') {
            $discountAmount = round($subTotal * $rate, 2);
        } else {
            $discountAmount = $rate;
        }

        if ($discountAmount > $subTotal) {
            $discountAmount = $subTotal;
        }
    }

    // totals after discount
    $subTotalAfterDiscount = max(0, $subTotal - $discountAmount);
    $tax = round($subTotalAfterDiscount * $taxRate, 2);
    $total = $subTotalAfterDiscount + $tax;


    if (isset($_SESSION["user_fname"]) 
        && isset( $_SESSION["user_lname"] ) && isset( $_SESSION["user_email"] )) {
        $session_fname = $_SESSION["user_fname"];
        $session_lname = $_SESSION["user_lname"];
        $session_email = $_SESSION["user_email"];
    }

    // get address from db if exists
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("
            SELECT first_name, last_name, address1, address2, city, zipcode
            FROM addresses
            WHERE user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$_SESSION["user_id"]]);
        // get key value
        $existingAddress = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existingAddress) {
            $address1Db = $existingAddress["address1"] ?? "";
            $address2Db = $existingAddress["address2"] ?? "";
            $cityDb = $existingAddress["city"] ?? "";
            $zipDb = $existingAddress["zipcode"]?? "";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delivery'])) {
        $deliveryOption = $_POST['delivery'] === 'pickup' ? 'pickup' : 'delivery';
    } else {
        $deliveryOption = $_SESSION['checkout_delivery']['option'] ?? 'delivery';
    }

    // delivery form submit
    if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delivery'])) {
        // validate form
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if ($firstName === '') {
            $deliveryErrors[] = "First name is required.";
        }

        if ($lastName === '') {
            $deliveryErrors[] = "Last name is required.";
        }

        if ($deliveryOption === 'delivery') {
            $address1 = trim($_POST['address1'] ?? '');
            $address2 = trim($_POST['address2'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $zip = trim($_POST['zipcode'] ?? '');

            if ($address1 === '') {
                $deliveryErrors[] = 'Address line 1 required for delivery';
            }
            if ($city === '') {
                $deliveryErrors[] = 'City is required for delivery';
            } 
            if ($zip === '') {
                $deliveryErrors[] = "ZIP code is required for delievery";
            }
        }

        if (empty($deliveryErrors)) {
            $deliverySuccess = true;
            $scrollToPayment = true;
            $_SESSION["checkout_checkpoints"]["delivery"] = true;
            
            // store delivery info for later
            $_SESSION['checkout_delivery'] = [
                'option'    => $deliveryOption,
                'firstName' => $firstName,
                'email'     => $email,
                'phone'     => $phone,
                'lastName'  => $lastName,
                'address1'  => $_POST['address1'] ?? null,
                'address2'  => $_POST['address2'] ?? null,
                'city'      => $_POST['city'] ?? null,
                'zipcode'   => $_POST['zipcode'] ?? null,
            ];

            // save address info to db if in session and is a delivery
            if ($_SESSION['checkout_delivery']['option'] === 'delivery'){
                if (isset($_SESSION['user_id'])) {
                    // prepare insert statement
                    $stmt = $pdo->prepare("
                        INSERT INTO addresses(
                            user_id,
                            first_name,
                            last_name,
                            address1,
                            address2,
                            city,
                            zipcode
                        ) VALUES (
                            ?,?,?,?,?,?,?
                        )
                        ON DUPLICATE KEY UPDATE
                            first_name = VALUES(first_name),
                            last_name = VALUES(last_name),
                            address1 = VALUES(address1),
                            address2 = VALUES(address2),
                            city = VALUES(city),
                            zipcode = VALUES(zipcode)
                    ");

                    $stmt->execute([
                        $_SESSION["user_id"],
                        $firstName,
                        $lastName,
                        $address1,
                        $address2,
                        $city,
                        $zip,
                    ]);
                }
            }

            // redirect
            header("Location: index.php?page=checkout&step=address_saved");
            exit;
            
        } else {
            if (isset($_SESSION["checkout_checkpoints"]["delivery"])) {
                unset($_SESSION["checkout_checkpoints"]["delivery"]);
            }
            $deliverySuccess = false;

        }
    }
    if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST["promo"])) {
        $promoCode = trim($_POST["promo-input"] ?? '');

        if ($promoCode === '') {
            $discountError = "Please enter a promo code.";
            unset($_SESSION["discount"]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM discounts WHERE name = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$promoCode]);
            $discount = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$discount) {
                $discountError = "Invalid or inactive discount code.";
                unset($_SESSION["discount"]);
            } else {
                $rate = (float)$discount['rate'];
                $type = $discount['type'] ?? 'percent'; 

                $_SESSION['discount'] = [
                    'id'   => $discount['id'],
                    'name' => $discount['name'],
                    'rate' => $rate,
                    'type' => $type,
                ];

                header("Location: index.php?page=checkout&step=discount_applied");
                exit;
            }
        }
    }


    // apply discount to totals
    $subTotalAfterDiscount = max(0, $subTotal - $discountAmount);
    $tax = round($subTotalAfterDiscount * $taxRate, 2);
    $total = $subTotalAfterDiscount + $tax;

    

    // payment form validation
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["checkout"])) {
        $cardNumberRaw = $_POST["card-input"] ?? '';
        $expRaw = $_POST['expiry-date'] ?? '';
        $cvvRaw = $_POST['cvv-input'] ?? '';

        // card input
        $cardDigits = preg_replace('/\D/', '', $cardNumberRaw);

        if ($cardDigits === '' || strlen($cardDigits) !== 16) {
            $paymentErrors["card-input"] = "Please enter a valid 16-digit card number.";
        }

        // expiry date check
        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $expRaw)) {
            $paymentErrors["exp-date"] = "Expiry date must be in MM/YYYY format";
        } else {
            [$mm, $yyyy] = explode("/", $expRaw);
            $mm = (int)$mm;
            $yyyy = (int)$yyyy;
        }

        // cvv check
        if (!preg_match('/^\d{3,4}$/', $cvvRaw)) {
            $paymentErrors["cvv"] = "CVV must be 3 or digits";
        }

        // process payment if no errors
        if (empty($paymentErrors)) {
            // get order data
            $userId = $_SESSION['user_id'] ?? null;
            $deliveryType = $_SESSION['checkout_delivery']['option'] ?? 'pickup';

            if ($deliveryType === 'delivery') {
                $address1 = $_SESSION['checkout_delivery']['address1'];
                $address2 = $_SESSION['checkout_delivery']['address2'];
                $city = $_SESSION['checkout_delivery']['city'];
                $zipcode = $_SESSION['checkout_delivery']['zipcode'];
            } else {
                $adddress1 = $address2 = $city = $zipcode = null;
            }

            $cardNumber = substr($cardDigits, -4);

            // begin transaction
            try {
                $pdo->beginTransaction();

                // insert into orders table
                $stmtOrder = $pdo->prepare("
                    INSERT INTO orders (
                        user_id,
                        delivery_type,
                        address1,
                        address2,
                        city,
                        zipcode,
                        subtotal,
                        tax,
                        total,
                        card_number
                    ) VALUES (?, ?,?,?, ?,?,?, ?,?,?)
                ");

                // execute order insert
                $stmtOrder->execute([
                    $userId,
                    $deliveryType,
                    $address1,
                    $address2,
                    $city,
                    $zipcode,
                    $subTotalAfterDiscount,
                    $tax,
                    $total,
                    $cardNumber
                ]);

                $orderId = $pdo->lastInsertId();
                
                // now insert order_items
                $stmtItem = $pdo->prepare("
                    INSERT INTO order_items (
                        order_id,
                        product_id,
                        quantity,
                        unit_price,
                        line_total
                    ) VALUES (?,?,?,?,?)
                ");

                foreach ($cart as $item) {
                    $lineTotal = $item['qty'] * $item['price'];

                    $stmtItem->execute([
                        $orderId,
                        $item['id'],
                        $item['qty'],
                        $item['price'],
                        $lineTotal
                    ]);
                }

                // commit both statements
                $pdo->commit();
                
                // clear certain session variables
                unset($_SESSION['cart']);
                unset($_SESSION['checkout_delivery']);
                unset($_SESSION['discount']);
                unset($_SESSION['flash']);
                unset($_SESSION["checkout_checkpoints"]);
                header("Location: index.php?page=placeorder&order_id=" . $orderId . "&delivery_type=" . $deliveryType);
                exit;
            } catch (PDOException $e) {
                $pdo->rollBack();
                $paymentErrors['db'] = "Database error placing order";
                error_log($e->getMessage());
            }
        }
    }

?>

<main>
    <h1>CHECKOUT</h1>
    <!-- delivery form -->
    <div class="wrapper">
    <div class="checkout-main">
        <div class="checkout-container">
            <form method="post"class="checkout-box delivery">
                <h2 class="box-title">1. DELIVERY OPTIONS</h2>
                <?php if (!empty($deliveryErrors)) : ?>
                    <div class="checkout-errors">
                        <ul>
                            <?php foreach ($deliveryErrors as $err) : ?>
                                <li><?=htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif;?>
                <div class="flex-row delivery-choice">
                    <label class="delivery-btn">
                        <input 
                            type="radio" 
                            name="delivery" 
                            value="delivery" 
                            <?= $deliveryOption === 'delivery' ? 'checked' : '' ?>
                        >
                        <span>DELIVERY</span>
                    </label>

                    <label class="delivery-btn">
                        <input 
                            type="radio" 
                            name="delivery" 
                            value="pickup"
                            <?= $deliveryOption === 'pickup' ? 'checked' : '' ?>
                        >
                        <span>PICK UP</span>
                    </label>

                </div>
                <div class="flex-row">
                    <div class="flex-column">
                        <label for="first_name">First</label>
                        <input
                            type="text"
                            placeholder="First Name"
                            name="first_name"
                            value="<?= htmlspecialchars($session_fname ? $session_fname : ($_SESSION['checkout_delivery']['firstName'] ?? '')) ?>"

                        />
                    </div>
                    <div class="flex-column">
                        <label for="last_name">Last</label>
                        <input
                            type="text"
                            placeholder="Last Name"
                            name="last_name"
                            value="<?= htmlspecialchars($session_lname ? $session_lname : ($_SESSION['checkout_delivery']['lastName'] ?? '' )) ?>"
                        />

                    </div>
                    
                </div>
                <!-- pickup method -->
                <div class="delivery-div" id="pickupFields">
                    <p>You selected pickup</p>
                </div>
                <!-- delivery method -->
                <div class="delivery-div" id="deliveryFields">
                    <label for="address1">Address Line 1:</label>
                    <input
                        type="text"
                        name="address1"
                        placeholder="Street Address, company name, etc"
                        value="<?= htmlspecialchars($address1Db ? $address1Db : ($_SESSION['checkout_delivery']['address1']??''))?>"
                    />
                    <label for="address2">Address Line 2:</label>
                    <input
                        type="text"
                        name="address2"
                        placeholder="Apartment, suite, unit, building, floor, etc"
                        value="<?= htmlspecialchars($address2Db ? $address2Db : ($_SESSION['checkout_delivery']['address2'] ?? ''))?>"
                    />
                    <label for="city">City:</label>
                    <input
                        type="text"
                        name="city"
                        placeholder="Your City"
                        value="<?= htmlspecialchars($cityDb ? $cityDb : ($_SESSION['checkout_delivery']['city'] ?? ''))?>"
                    />
                    <label for="zipcode">ZIP</label>
                    <input
                        type="number"
                        name="zipcode"
                        placeholder="ZIP / Postal Code"
                        value="<?= htmlspecialchars(string: $zipDb ? $zipDb : ($_SESSION['checkout_delivery']['zipcode'] ?? ''))?>"
                    />
                </div>
                <div class="flex-row">
                    <div class="flex-column">
                        <label for="email">Email</label>
                        <input type="email"
                            name="email"
                            value="<?= htmlspecialchars($session_email ? $session_email : ($_SESSION['checkout_delivery']['email'] ?? ''))?>"
                            placeholder="Email"
                        />
                    </div>

                    <div class="flex-column">
                        <label for="phone">Phone</label>
                        <input 
                            type="tel"
                            name="phone"
                            placeholder="Phone number"
                            value="<?= htmlspecialchars(($_SESSION['checkout_delivery']['phone'] ?? ''))?>"
                        />
                    </div>
                </div>

                <button class="btn primary" type="submit">Save & Continue</button>
            </form>

            <!-- payment form -->
            <form method="post" id="payment-box" class="checkout-box payment <?=$deliverySuccess ? '' : 'disabled'?>">
                <h2 class="box-title">2. PAYMENT</h2>
                <p
                    id="payment-locked-msg"
                    class="<?=$deliverySuccess ? 'is-hidden' : ''?>"
                >
                    If you change your delivery or pickup option, please save your changes.
                </p>
                <label for="card-input">Card Number</label>
                <input
                    name="card-input"
                    type="text"
                    id = "card-input"
                    class="card-input"
                    placeholder="1234 5678 9012 3456"
                    value="<?= htmlspecialchars($_POST['card-input']??'') ?>"
                />
                <?php if (!empty($paymentErrors['card-input'])) : ?>
                    <p class="error-msg"><?=htmlspecialchars($paymentErrors['card-input']) ?></p>
                <?php endif; ?>
                <div class="flex-row">
                    <div class="flex-column">
                        <label for="exp-date">Expiry Date</label>
                        <input
                            name="expiry-date"
                            id ="expiry-date"
                            type="text"
                            placeholder="MM/YYYY"
                            maxlength="7"
                        />
                        <?php if (!empty($paymentErrors['exp-date'])) : ?>
                            <p class="error-msg"><?=htmlspecialchars($paymentErrors['exp-date']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="flex-column">
                        <label for="cvv2">CVV/CVV2</label>
                        <input
                            name="cvv-input"
                            id="cvv-input"
                            type="text"
                            placeholder="CVV/CVV2"
                            maxlength="4"
                            minlength="3"
                        />
                        <?php if (!empty($paymentErrors['cvv'])) : ?>
                            <p class="error-msg"><?=htmlspecialchars($paymentErrors['cvv']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <button class="btn checkout" id="checkout-btn" name="checkout" type="submit">Checkout</button>
            </form>
        </div>  
        <div class="total-bar">
            <div class="order-summary-list">
                <?php foreach ($cart as $item): ?>
                    <?php
                        $id        = $item['id'];
                        $name      = $item['name'];
                        $price     = $item['price'];
                        $qty       = $item['qty'];
                        $imgSmall  = $item['image_small'] ?? '';
                    ?>
                    
                    <div class="summary-item">
                        <div class="summary-item-left">
                            <?php if (!empty($imgSmall)): ?>
                                <img
                                    src="<?= htmlspecialchars($imgSmall) ?>"
                                    alt="<?= htmlspecialchars($name) ?>"
                                    class="summary-thumb"
                                />
                            <?php endif; ?>

                            <div class="summary-item-details">
                                <p class="summary-item-name">
                                    <?= htmlspecialchars($name) ?>
                                </p>
                                <p class="summary-item-link">
                                    <a href="index.php?page=product&id=<?= $id ?>">
                                        View details
                                    </a>
                                </p>
                            </div>
                        </div>

                        <div class="summary-item-right">
                            <span class="summary-qty">Qty: <?= $qty ?></span>
                            <span class="summary-price">$<?= number_format($price, 2) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="post">
                <label for="promo">Promo Code</label>
                <div class="grid-column-2">
                        <input
                        class="promo-input"
                        type="text"
                        name="promo-input"
                        placeholder="Enter Promo Code"
                        value="<?=htmlspecialchars($_POST['promo-input'] ?? ($_SESSION['discount']['name'] ?? ''))?>"
                        />
                        <button class="btn primary" type="submit" name="promo">Apply</button>
                </div>
                <?php if (!empty($discountError)) : ?>
                    <p class="error-msg"><?=htmlspecialchars($discountError)?></p>
                <?php endif; ?>
            </form>
            <p>Subtotal: $<?=htmlspecialchars(number_format($subTotal, 2))?></p>
            <?php if ($discountAmount > 0): ?>
                <p>Discount: -$<?=htmlspecialchars(number_format($discountAmount, 2))?></p>
            <?php endif; ?>
            <p>Tax: $<?=htmlspecialchars(number_format($tax, 2))?></p>
            <p><strong>Total: $<?=htmlspecialchars(number_format($total, 2))?></strong></p>
        </div>
    </div>
    </div>
</main>

<script src="./js/checkout.js"></script>