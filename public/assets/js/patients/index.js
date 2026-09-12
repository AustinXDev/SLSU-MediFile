import { patientEvents } from "./components/patientEvents.js";
import { patientData } from "./components/patientData.js";
import { patientTable } from "./components/patientTable.js";

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
    await patientData.load();

    patientEvents.bind();
    patientTable.render();
  } catch (error) {
    console.error("Unable to load patient records:", error);
  }
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", init);
} else {
  init();
}

window.openSidebar = openSidebar;
window.closeSidebar = closeSidebar;
