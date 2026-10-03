import { activityData } from "./components/activityData.js";
import { activityEvents } from "./components/activityEvents.js";

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
    activityEvents.bind();
    await activityData.load();
    activityEvents.startRefresh();
  } catch (error) {
    console.error("Unable to initialize activity logs:", error);
  }
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", init, { once: true });
} else {
  init();
}

window.openSidebar = openSidebar;
window.closeSidebar = closeSidebar;
