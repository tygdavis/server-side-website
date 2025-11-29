document.addEventListener("DOMContentLoaded", () => {
	const navToggle = document.querySelector(".nav-toggle");
	const navLinks = document.querySelector(".nav-links");

	if (!navToggle || !navLinks) return;

	const navIcon = navToggle.querySelector("img");

	navToggle.addEventListener("click", () => {
		const isOpen = navLinks.classList.toggle("is-open");

		navToggle.classList.toggle("is-open", isOpen);

		if (isOpen) {
			navIcon.src = "assets/icons/closeMenu.png";
			navIcon.alt = "close";
		} else {
			navIcon.src = "assets/icons/openMenu.png";
			navIcon.alt = "open";
		}

		navIcon.classList.toggle("is-open", isOpen);
	});
});
