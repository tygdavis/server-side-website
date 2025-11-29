document.addEventListener("DOMContentLoaded", () => {
	const form = document.getElementById("contact-form");
	if (!form) return;

	const firstInput = document.getElementById("first");
	const lastInput = document.getElementById("last");
	const emailInput = document.getElementById("email");
	const phoneInput = document.getElementById("phone");
	const messageInput = document.getElementById("message");

	// gets then nearest .field from the input
	function getFieldWrapper(input) {
		return input.closest(".field");
	}

	// sets the error emssage
	function setError(input, message) {
		const field = getFieldWrapper(input);
		if (!field) return;

		field.classList.add("has-error");

		let errorEl = field.querySelector(".field-error");
		if (!errorEl) {
			errorEl = document.createElement("small");
			errorEl.className = "field-error";
			field.appendChild(errorEl);
		}

		errorEl.textContent = message;
	}

	// clears errors
	function clearError(input) {
		const field = getFieldWrapper(input);
		if (!field) return;

		field.classList.remove("has-error");

		const errorEl = field.querySelector(".field-error");
		// Only clear client-side error if it exists
		if (errorEl && !errorEl.dataset.server) {
			errorEl.textContent = "";
		}
	}

	// Email validation using regex
	function isValidEmail(email) {
		const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		return pattern.test(email);
	}

	// prevent typing non digits
	if (phoneInput) {
		phoneInput.addEventListener("input", (e) => {
			e.target.value = e.target.value.replace(/\D/g, "");
		});
	}

	// validates phone
	function isValidPhone(phone) {
		const cleaned = phone.replace(/\D/g, ""); // only digits
		if (cleaned === "") return true;
		return cleaned.length === 10; // 10 digits
	}

	function validateField(input) {
		if (!input) return true;

		const value = input.value.trim();
		clearError(input);

		// First name
		if (input === firstInput) {
			if (value === "") {
				setError(input, "First name is required.");
				return false;
			}
		}

		// Last name
		if (input === lastInput) {
			if (value === "") {
				setError(input, "Last name is required.");
				return false;
			}
		}

		// Email
		if (input === emailInput) {
			if (value === "") {
				setError(input, "Email is required.");
				return false;
			}
			if (!isValidEmail(value)) {
				setError(input, "Please enter a valid email address.");
				return false;
			}
		}

		// Phone validation
		if (input === phoneInput) {
			if (value !== "" && !isValidPhone(value)) {
				setError(input, "Please enter a valid phone number.");
				return false;
			}
		}

		// message validation
		if (input === messageInput) {
			if (value === "") {
				setError(input, "Please enter a message.");
				return false;
			}
		}

		return true;
	}

	// Validate on blur
	[firstInput, lastInput, emailInput, phoneInput, messageInput].forEach(
		(input) => {
			if (!input) return;
			input.addEventListener("blur", () => validateField(input));
		}
	);

	// Validate on submit
	form.addEventListener("submit", (e) => {
		let isValid = true;

		if (!validateField(firstInput)) isValid = false;
		if (!validateField(lastInput)) isValid = false;
		if (!validateField(emailInput)) isValid = false;
		if (!validateField(phoneInput)) isValid = false;
		if (!validateField(messageInput)) isValid = false;

		if (!isValid) {
			e.preventDefault();
			// scroll to error
			const firstErrorField = form.querySelector(".has-error");
			if (firstErrorField) {
				firstErrorField.scrollIntoView({ behavior: "smooth", block: "center" });
			}
		}
	});
});
