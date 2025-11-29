<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['action'])) {
    $productId = (int)$_POST['product_id'];
    $action    = $_POST['action'];

    // fetch product from DB
    $stmt = $pdo->prepare("
        SELECT id, name, price, category, image_large, image_medium, image_small, description
        FROM products
        WHERE id = :id
    ");
    $stmt->execute(['id' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['qty']++;
        } else {
            $_SESSION['cart'][$productId] = [
                'id'          => $product['id'],
                'name'        => $product['name'],
                'price'       => $product['price'],
                'qty'         => 1,
                'image_small' => $product['image_small'] ?? null,
            ];
        }

        $_SESSION['flash'] = "Added {$product['name']} to your cart.";
    }

    if ($action === 'buy') {
        // go straight to cart
        header("Location: index.php?page=checkout");
        exit;
    } else {
        header("Location: index.php?page=products&step=product_" . $productId . "_added");
        exit;
    }
}

// apply filters
$categoryFilters = isset($_GET['category']) ? (array)$_GET['category'] : [];
$priceFilters    = isset($_GET['price'])    ? (array)$_GET['price']    : [];

$categoryFilters = array_map('strtolower', $categoryFilters);
$priceFilters    = array_map('strtolower', $priceFilters);

$where  = [];
$params = [];

// category IN (...)
if (!empty($categoryFilters)) {
    $placeholders = [];
    foreach ($categoryFilters as $i => $cat) {
        $key = ":cat{$i}";
        $placeholders[] = $key;
        $params[$key] = $cat;
    }
    // assume category stored in lowercase, otherwise wrap with LOWER()
    $where[] = "LOWER(category) IN (" . implode(',', $placeholders) . ")";
}

// price ranges (under5, 5to50, 50plus)
if (!empty($priceFilters)) {
    $priceConds = [];
    foreach ($priceFilters as $pf) {
        if ($pf === 'under5') {
            $priceConds[] = "price < 5";
        } elseif ($pf === '5to50') {
            $priceConds[] = "price BETWEEN 5 AND 50";
        } elseif ($pf === '50plus') {
            $priceConds[] = "price > 50";
        }
    }
    if (!empty($priceConds)) {
        $where[] = "(" . implode(" OR ", $priceConds) . ")";
    }
}

// base query
$sql = "
    SELECT id, name, category, price, description,
           image_large, image_medium, image_small
    FROM products
";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<!--products page -->
<main class="page">
	<div class="main-container" id="main-container">
		<div class="side-bar-container">
			<aside class="side-bar" id="side-bar">
				<form method="get" action="index.php">
					<input type="hidden" name="page" value="products" />

					<div class="filter-group">
						<h3>Category</h3>
						<menu>
							<li>
								<label>
									<input
										name="category[]"
										value="bread"
										type="checkbox"
										<?= in_array('bread', $categoryFilters, true) ? 'checked' : '' ?>
									/>
									Bread
								</label>
								<label>
									<input
										name="category[]"
										value="pastry"
										type="checkbox"
										<?= in_array('pastry', $categoryFilters, true) ? 'checked' : '' ?>
									/>
									Pastries
								</label>
								<label>
									<input
										name="category[]"
										value="soup"
										type="checkbox"
										<?= in_array('soup', $categoryFilters, true) ? 'checked' : '' ?>
									/>
									Soup
								</label>
							</li>
						</menu>
					</div>

					<div class="filter-group">
						<h3>Price</h3>
						<menu>
							<li>
								<label>
									<input
										name="price[]"
										value="under5"
										type="checkbox"
										<?= in_array('under5', $priceFilters, true) ? 'checked' : '' ?>
									/>
									Under $5
								</label>
							</li>
							<li>
								<label>
									<input
										name="price[]"
										value="5to50"
										type="checkbox"
										<?= in_array('5to50', $priceFilters, true) ? 'checked' : '' ?>
									/>
									$5 - $50
								</label>
							</li>
							<li>
								<label>
									<input
										name="price[]"
										value="50plus"
										type="checkbox"
										<?= in_array('50plus', $priceFilters, true) ? 'checked' : '' ?>
									/>
									$50+
								</label>
							</li>
						</menu>
					</div>

					<button type="submit" class="btn primary">Apply Filters</button>
				</form>
			</aside>

			<div class="btn-container">
				<button onclick="toggleSidebar()" class="sidebar-btn" id="sidebar-btn">
					<img
						id="toggle-sidebar-btn"
						class="icon"
						src="assets/icons/down.png"
					/>
				</button>
			</div>
		</div>

		<div class="container">
			<h3 class="product-title">Browse Our Products</h3>

			<?php if ($flash): ?>
				<div class="flash-message">
					<p><?= htmlspecialchars($flash) ?></p>
				</div>
			<?php endif; ?>

			<ul class="products-container" id="products-container">
				<?php if (empty($result)): ?>
					<li class="no-product">
						<p>No products found.</p>
					</li>
				<?php else: ?>
					<?php foreach ($result as $p): ?>
						<li
							class="product-container"
							data-id="<?= htmlspecialchars($p['id']) ?>"
							data-category="<?= htmlspecialchars($p['category']) ?>"
							data-price="<?= htmlspecialchars($p['price']) ?>"
						>
							<a
								href="index.php?page=product&id=<?= htmlspecialchars($p['id']) ?>"
								class="product-link"
							>
								<h4 class="title">
									<?= htmlspecialchars($p['name']) ?>
								</h4>

								<div class="product-img-wrapper">
									<picture>
										<?php if (!empty($p['image_large'])): ?>
											<source
												srcset="<?= htmlspecialchars($p['image_large']) ?>"
												media="(min-width: 1024px)"
											/>
										<?php endif; ?>

										<?php if (!empty($p['image_medium'])): ?>
											<source
												srcset="<?= htmlspecialchars($p['image_medium']) ?>"
												media="(min-width: 768px)"
											/>
										<?php endif; ?>

										<?php if (!empty($p['image_small'])): ?>
											<source
												srcset="<?= htmlspecialchars($p['image_small']) ?>"
												media="(max-width: 767px)"
											/>
										<?php endif; ?>

										<?php
											$imgSrc = $p['image_medium']
												?: ($p['image_large'] ?: $p['image_small']);
										?>
										<?php if (!empty($imgSrc)): ?>
											<img
												class="product-pic"
												src="<?= htmlspecialchars($imgSrc) ?>"
												alt="<?= htmlspecialchars($p['name']) ?>"
												loading="lazy"
											/>
										<?php endif; ?>
									</picture>
								</div>

								<h5 class="price-text">
									&dollar;<?= number_format((float)$p['price'], 2) ?>
								</h5>

								<p class="description">
									<?= htmlspecialchars($p['description']) ?>
								</p>
							</a>

							<div class="two-btn-container">
								<form method="post" action="index.php?page=products" class="inline-form">
									<input
										type="hidden"
										name="product_id"
										value="<?= htmlspecialchars($p['id']) ?>"
									/>
									<input type="hidden" name="action" value="buy" />
									<button
										class="submit-bar submit-btn primary"
										type="submit"
									>
										Buy Now
									</button>
								</form>

								<form method="post" action="index.php?page=products" class="inline-form">
									<input
										type="hidden"
										name="product_id"
										value="<?= htmlspecialchars($p['id']) ?>"
									/>
									<input type="hidden" name="action" value="add" />
									<button
										class="submit-bar submit-btn secondary add-to-cart"
										type="submit"
									>
										Add to Cart
									</button>
								</form>
							</div>
						</li>
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</main>
<script src="./js/products.js"></script>
