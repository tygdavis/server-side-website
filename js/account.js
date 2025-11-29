document.addEventListener("DOMContentLoaded", () => {
	const form = document.getElementById("update-pass-form");
	const passwordFields = document.querySelectorAll(".password");
	const jsErrorInput = document.getElementById("js_error");

	passwordFields.forEach((el) => {
		el.addEventListener("keydown", (e) => {
			if (e.key === " " || e.code === "Space") {
				e.preventDefault();
			}
		});
	});
	// password validation
	function isStrongPassword(pw) {
		const hasUpper = /[A-Z]/.test(pw);
		const hasSpecial = /[!@#$%^&*(),.?":{}|<>_\-+=/\\\[\]]/.test(pw);
		const hasValidLength = pw.length >= 6;
		const hasNoSpaces = !/\s/.test(pw);

		return hasUpper && hasSpecial && hasValidLength && hasNoSpaces;
	}

	form.addEventListener("submit", (e) => {
		let valid = true;
		let errorMessage = "";

		// check all password fields
		passwordFields.forEach((field) => {
			const value = field.value.trim();
			if (!isStrongPassword(value)) {
				valid = false;
				errorMessage =
					"Password must be at least 6 characters, include 1 uppercase letter, and 1 special character.";
			}
		});

		// if passwords don’t match
		if (passwordFields.length === 2) {
			const p1 = passwordFields[0].value.trim();
			const p2 = passwordFields[1].value.trim();
			if (p1 !== p2) {
				valid = false;
				errorMessage = "Passwords do not match.";
			}
		}

		jsErrorInput.value = errorMessage;

		if (!valid) {
			e.preventDefault();
		}
	});
});
