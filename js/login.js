document.addEventListener("DOMContentLoaded", () => {
	const form = document.getElementById("loginForm");
	const emailInput = document.getElementById("email");
	const passwordInput = document.getElementById("password");

	const emailError = document.getElementById("emailError");
	const passwordError = document.getElementById("passwordError");

	const allErrors = document.querySelectorAll(".field-error");

	// toggle display on errors if no text content
	function toggleHidden(el) {
		if (el.textContent.trim() === "") {
			el.classList.add("is-hidden");
		} else {
			el.classList.remove("is-hidden");
		}
	}
	allErrors.forEach((err) => {
		toggleHidden(err);
	});
	// prevent spaces from being types in password field
	passwordInput.addEventListener("keydown", (e) => {
		if (e.key === " " || e.code === "Space") {
			e.preventDefault();
		}
	});
	// regex for email
	function isValidEmail(email) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
	}

	form.addEventListener("submit", (e) => {
		let valid = true;

		// reset all errors
		emailError.textContent = "";
		passwordError.textContent = "";

		// email validation
		if (!isValidEmail(emailInput.value.trim())) {
			emailError.textContent = "Please enter a valid email address.";
			valid = false;
		}

		// prevent form from submitting if not valid
		if (!valid) {
			e.preventDefault();
		}
	});
});
