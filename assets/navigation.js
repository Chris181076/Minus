document.addEventListener("DOMContentLoaded", () => {
  // Menu Burger
  let isMenuOpen = false;
  const burger = document.getElementById("burger");
  const burgerIcon = document.getElementById("burger-icon");
  const menu = document.getElementById("menu");

  if (burger && menu && burgerIcon) {
    burger.addEventListener("click", () => {
      isMenuOpen = !isMenuOpen;
      menu.classList.toggle("show");
      burgerIcon.src = isMenuOpen 
        ? "/Decor/close.png"  
        : "/Decor/burger.png";
    });
  }

  // Sidebar
  let isSidebarOpen = false;
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebar = document.getElementById("sidebar");

  if (sidebarToggle && sidebar) {
    sidebarToggle.textContent = "☰ MenuMinus";
    sidebarToggle.addEventListener("click", () => {
      isSidebarOpen = !isSidebarOpen;
      sidebar.classList.toggle("sidebar-visible");
      sidebarToggle.textContent = isSidebarOpen ? "✕ Fermer" : "☰ MenuMinus";
    });
  }
});
