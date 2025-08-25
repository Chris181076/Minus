document.addEventListener("DOMContentLoaded", () => {
  // 🔹 Menu Burger
  let isMenuOpen = false;
  const burger = document.getElementById("burger");
  const menu = document.getElementById("menu");

  if (burger && menu) {
    burger.textContent = "☰";
    burger.addEventListener("click", () => {
      isMenuOpen = !isMenuOpen;
      menu.classList.toggle("menu-visible");
      burger.textContent = isMenuOpen ? "✕" : "☰";
    });
  }

  // 🔹 Sidebar
  let isSidebarOpen = false;
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebar = document.getElementById("sidebar");

  if (sidebarToggle && sidebar) {
    sidebarToggle.textContent = "☰ Menu";
    sidebarToggle.addEventListener("click", () => {
      isSidebarOpen = !isSidebarOpen;
      sidebar.classList.toggle("sidebar-visible");
      sidebarToggle.textContent = isSidebarOpen ? "✕ Fermer" : "☰ Menu";
    });
  }
});