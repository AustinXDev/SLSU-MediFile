import { patientEvents } from "./components/patientEvents.js";
import { serviceEvents } from "./components/serviceEvents.js";
import { patientData } from "./components/patientData.js";
import { patientTable } from "./components/patientTable.js";
import { fitDocumentPages } from "./components/fitDocumentsPages.js";
import { bindLogout } from "../components/logout.js";

function openSidebar() {
  document.getElementById("sidebar")?.classList.add("sidebar-open");
  document.getElementById("sidebarOverlay")?.classList.add("active");
}

function closeSidebar() {
  document.getElementById("sidebar")?.classList.remove("sidebar-open");
  document.getElementById("sidebarOverlay")?.classList.remove("active");
}

async function init() {
  bindLogout();

  try {
    await patientData.loadPatients();

    patientEvents.bind();
    serviceEvents.init();
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
window.addEventListener("resize", fitDocumentPages);
