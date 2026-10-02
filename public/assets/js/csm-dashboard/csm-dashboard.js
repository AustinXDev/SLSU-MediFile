import { dashboard } from "./components/renderCsmDashboard.js";

function openSidebar() {
  document.getElementById("sidebar")?.classList.add("sidebar-open");
  document.getElementById("sidebarOverlay")?.classList.add("active");
}

function closeSidebar() {
  document.getElementById("sidebar")?.classList.remove("sidebar-open");
  document.getElementById("sidebarOverlay")?.classList.remove("active");
}

document.addEventListener("DOMContentLoaded", () => {
  dashboard.init();
});

window.openSidebar = openSidebar;
window.closeSidebar = closeSidebar;
