const sidebar = document.getElementById("side-bar");
const sidebarBtn = document.getElementById("sidebar-btn");
const desktop = window.matchMedia("(max-width: 768px)");
const toggleSidebarBtn = document.getElementById("toggle-sidebar-btn");

function toggleSidebar() {
	if (!desktop.matches) return;
	sidebar.classList.toggle("hide");
	const hidden = sidebar.classList.contains("hide");
	toggleSidebarBtn.src = hidden
		? "assets/icons/up.png"
		: "assets/icons/down.png";
}

window.toggleSidebar = toggleSidebar;
// reset the sidebar when desktop size
desktop.addEventListener("change", (e) => {
	if (!e.matches) {
		sidebar.classList.remove("hide");
		if (sidebarBtn) {
			sidebarBtn.textContent = "Close";
		}
	}
});
