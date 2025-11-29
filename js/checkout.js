document.addEventListener("DOMContentLoaded", () => {
	// delivery option toggle
	const deliveryRadio = document.querySelector('input[value="delivery"]');
	const pickupRadio = document.querySelector('input[value="pickup"]');

	const deliveryFields = document.getElementById("deliveryFields");
	const pickupFields = document.getElementById("pickupFields");

	const paymentBox = document.getElementById("payment-box");
	const checkoutBtn = document.getElementById("checkout-btn");
	const paymentLockedMsg = document.getElementById("payment-locked-msg");

	function toggleFields() {
		if (!pickupRadio || !deliveryFields || !pickupFields) return;

		if (pickupRadio.checked) {
			pickupFields.classList.remove("is-hidden");
			deliveryFields.classList.add("is-hidden");
		} else {
			pickupFields.classList.add("is-hidden");
			deliveryFields.classList.remove("is-hidden");
		}
	}

	function setPaymentLocked(locked) {
		if (!paymentBox || !checkoutBtn) return;

		if (locked) {
			paymentBox.classList.add("disabled");
			checkoutBtn.disabled = true;
			if (paymentLockedMsg) paymentLockedMsg.classList.remove("is-hidden");
		} else {
			paymentBox.classList.remove("disabled");
			checkoutBtn.disabled = false;
			if (paymentLockedMsg) paymentLockedMsg.classList.add("is-hidden");
		}
	}

	function handleDeliveryChange() {
		toggleFields();
		setPaymentLocked(true);
	}

	// attach listeners
	if (deliveryRadio && pickupRadio) {
		deliveryRadio.addEventListener("change", handleDeliveryChange);
		pickupRadio.addEventListener("change", handleDeliveryChange);
		toggleFields();
	}

	if (paymentBox && checkoutBtn) {
		const locked = paymentBox.classList.contains("disabled");
		checkoutBtn.disabled = locked;
		if (paymentLockedMsg) {
			paymentLockedMsg.classList.toggle("is-hidden", !locked);
		}
	}

	// scroll to payment box
	if (paymentBox && paymentBox.classList.contains("scroll")) {
		paymentBox.scrollIntoView({ behavior: "smooth", block: "start" });
	}

	// payment inputs
	const cardInput = document.getElementById("card-input");
	const expiryInput = document.getElementById("expiry-date");
	const cvvInput = document.getElementById("cvv-input");

	// enable checkout btn based on errors
	function updateCheckoutButton() {
		if (!checkoutBtn || !cardInput || !expiryInput || !cvvInput) return;

		const hasError =
			cardInput.classList.contains("error") ||
			expiryInput.classList.contains("error") ||
			cvvInput.classList.contains("error") ||
			cardInput.value.trim() === "" ||
			expiryInput.value.trim() === "" ||
			cvvInput.value.trim() === "";

		checkoutBtn.disabled = hasError;
	}

	// cc validation
	if (cardInput) {
		// format credit card input
		cardInput.addEventListener("input", (e) => {
			// replace all non-digits
			let value = e.target.value.replace(/\D/g, "");

			// limit to 16 digits
			value = value.slice(0, 16);

			// insert space after every 4 digits
			let newVal = "";
			for (let i = 0; i < value.length; i++) {
				if (i > 0 && i % 4 === 0) {
					newVal += " ";
				}
				newVal += value[i];
			}
			e.target.value = newVal;
		});

		// validate credit card length
		cardInput.addEventListener("change", () => {
			// strip spaces
			const raw = cardInput.value.replace(/\s/g, "");

			if (raw.length === 16) {
				cardInput.classList.remove("error");
			} else {
				cardInput.classList.add("error");
			}
			updateCheckoutButton();
		});
	}

	// expiry date validation
	if (expiryInput) {
		expiryInput.addEventListener("input", (e) => {
			// remove all non-digits
			let value = e.target.value.replace(/\D/g, "");

			// limit to 6 digits (MMYYYY)
			value = value.slice(0, 6);

			// auto insert /
			if (value.length > 2) {
				value = value.slice(0, 2) + "/" + value.slice(2);
			}

			e.target.value = value;
		});

		expiryInput.addEventListener("change", () => {
			const value = expiryInput.value;

			// must be MM/YYYY
			const isValidFormat = /^(\d{2})\/(\d{4})$/.test(value);
			if (!isValidFormat) {
				expiryInput.classList.add("error");
				updateCheckoutButton();
				return;
			}

			const [monthStr, yearStr] = value.split("/");
			const month = Number(monthStr);
			const year = Number(yearStr);

			// valid month 1–12
			if (month < 1 || month > 12) {
				expiryInput.classList.add("error");
				updateCheckoutButton();
				return;
			}

			const now = new Date();
			const exp = new Date(year, month - 1); // first day of exp month
			const thisMonth = new Date(now.getFullYear(), now.getMonth());

			// check if expired
			if (exp < thisMonth) {
				expiryInput.classList.add("error");
				updateCheckoutButton();
				return;
			}

			// check if too far into future
			if (exp.getFullYear() > now.getFullYear() + 5) {
				expiryInput.classList.add("error");
				updateCheckoutButton();
				return;
			}
			// everything is good
			expiryInput.classList.remove("error");
			updateCheckoutButton();
		});
	}

	// cvv validation
	if (cvvInput) {
		cvvInput.addEventListener("input", (e) => {
			// digits only, up to 4
			e.target.value = e.target.value.replace(/\D/g, "").slice(0, 4);
		});

		cvvInput.addEventListener("change", () => {
			const cvv = cvvInput.value;

			if (cvv.length === 3 || cvv.length === 4) {
				cvvInput.classList.remove("error");
			} else {
				cvvInput.classList.add("error");
			}
			updateCheckoutButton();
		});
	}

	// initial state
	updateCheckoutButton();
});
