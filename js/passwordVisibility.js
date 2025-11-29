document.addEventListener("DOMContentLoaded", () => {
	document
		.querySelectorAll(".password-field-container")
		.forEach((container) => {
			const input = container.querySelector(".password-field");
			const btn = container.querySelector(".toggle-visibility-btn");
			const img = container.querySelector(".toggle-visibility-btn img");

			if (!input || !btn) return;

			btn.addEventListener("click", () => {
				const hidden = input.type === "password";
				input.type = hidden ? "text" : "password";
				img.src = hidden
					? "assets/icons/hidePassword.png"
					: "assets/icons/showPassword.png";
			});
		});
});
