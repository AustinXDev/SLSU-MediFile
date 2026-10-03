import { dashboard } from "./components/renderDashboard.js";
import { bindLogout } from "../components/logout.js";

document.addEventListener("DOMContentLoaded", () => {
  bindLogout();
  dashboard.init();
});
