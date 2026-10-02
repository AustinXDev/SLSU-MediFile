import { userEvents } from "./components/userEvents.js";
import { userData } from "./components/userData.js";

function openSidebar() {
  document.getElementById("sidebar")?.classList.add("sidebar-open");
  document.getElementById("sidebarOverlay")?.classList.add("active");
}

function closeSidebar() {
  document.getElementById("sidebar")?.classList.remove("sidebar-open");
  document.getElementById("sidebarOverlay")?.classList.remove("active");
}

async function init() {
  try {
    await userData.loadAccounts();
    userEvents.bind();
  } catch (error) {
    console.error("Unable to load user accounts:", error);
  }
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", init);
} else {
  init();
}

window.openSidebar = openSidebar;
window.closeSidebar = closeSidebar;
