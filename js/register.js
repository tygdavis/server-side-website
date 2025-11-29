document.addEventListener("DOMContentLoaded", () => {
	const form = document.getElementById("registerForm");
	const fNameInput = document.getElementById("first_name");
	const lNameInput = document.getElementById("last_name");
	const emailInput = document.getElementById("email");
	const passwordInput = document.getElementById("password");

	const firstNameError = document.getElementById("firstNameError");
	const lastNameError = document.getElementById("lastNameError");
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

	// name validation
	// for first and last name
	function isValidName(name) {
		name = name.trim();

		// only letters, spaces hyphens, or apostrophes
		// at least one letter
		return /^[A-Za-z' -]+$/.test(name);
	}
	// regex for email
	function isValidEmail(email) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
	}

	// password validation
	// must be at least characters
	// must have 1 uppercase letter
	// must have one special character
	function isStrongPassword(pw) {
		const hasUpper = /[A-Z]/.test(pw);
		const hasSpecial = /[!@#$%^&*(),.?":{}|<>_\-+=/\\\[\]]/.test(pw);
		const hasValidLength = pw.length >= 6;
		const hasNoSpaces = !/\s/.test(pw);

		return hasUpper && hasSpecial && hasValidLength && hasNoSpaces;
	}

	form.addEventListener("submit", (e) => {
		let valid = true;

		// reset all errors
		firstNameError.textContent = "";
		lastNameError.textContent = "";
		emailError.textContent = "";
		passwordError.textContent = "";

		// first name validation
		if (!isValidName(fNameInput.value)) {
			firstNameError.textContent = "Not a valid name";
			valid = false;
		}

		if (!isValidName(lNameInput.value)) {
			lastNameError.textContent = "Not a valid name";
			valid = false;
		}

		if (!isValidEmail(emailInput.value.trim())) {
			// email validation
			emailError.textContent = "Please enter a valid email address.";
			valid = false;
		}

		const pw = passwordInput.value.trim();

		if (!isStrongPassword(pw)) {
			passwordError.textContent =
				"Password must be at least 6 characters, include 1 uppercase, and 1 special character!";
			valid = false;
		}

		allErrors.forEach((el) => toggleHidden(el));

		// prevent form from submitting if not valid
		if (!valid) {
			e.preventDefault();
		}
	});
});
