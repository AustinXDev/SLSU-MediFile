import { activityApi } from "./activityApi.js";
import { activityFilters } from "./activityFilters.js";
import { activitySummary } from "./activitySummary.js";
import { activityTable } from "./activityTable.js";
import { dom } from "../utils/dom.js";
import { state } from "./state.js";

export const activityData = {
  async load({ background = false } = {}) {
    const requestId = ++state.requestId;
    state.loading = true;
    this.setError();

    if (!background) {
      dom.get("activityResultSummary").textContent = "Loading activity...";
      dom.get("activityEmptyState").hidden = true;
      dom.get("activityTableBody").hidden = false;
      dom.get("activityTableBody").innerHTML =
        '<tr><td colspan="7">Loading activity...</td></tr>';
    }

    try {
      const data = await activityApi.getActivity(activityFilters.params());
      if (requestId !== state.requestId) return;

      const records = Array.isArray(data.records) ? data.records : [];
      activityFilters.setOptions(data.filters);
      activitySummary.render(data.summary);
      this.setViewTitle();
      this.setEmptyState("No activity records found.", "Try adjusting your search or filters.");
      dom.get("activityResultSummary").textContent =
        `Showing ${records.length} of ${Number(data.pagination?.total || 0).toLocaleString()} ${state.view === "patients" ? "patient activities" : "activities"}`;
      activityTable.render(records);
      dom.get("activityEmptyState").hidden = records.length !== 0;
      dom.get("activityTableBody").hidden = records.length === 0;
      activityTable.renderPagination(data.pagination || {});
    } catch (error) {
      if (requestId !== state.requestId) return;
      this.renderError(error);
    } finally {
      if (requestId === state.requestId) {
        state.loading = false;
      }
    }
  },

  setViewTitle() {
    dom.get("activityTableTitle").textContent =
      state.view === "patients" ? "Recent Patients" : "Activity Logs";
  },

  setEmptyState(title, description) {
    const empty = dom.get("activityEmptyState");
    empty.querySelector("h3").textContent = title;
    empty.querySelector("p").textContent = description;
  },

  renderError(error) {
    this.setError(error.message || "Unable to load activity logs.");
    dom.get("activityResultSummary").textContent = "Unable to load activity";
    dom.get("activityTableBody").innerHTML = "";
    dom.get("activityTableBody").hidden = true;
    dom.get("activityEmptyState").hidden = false;
    this.setEmptyState(
      "Activity data could not be loaded.",
      "Please try again in a moment.",
    );
    dom.get("activityPaginationSummary").textContent = "";
    dom.get("activityPagination").replaceChildren();
  },

  setError(message = "") {
    const error = dom.get("activityError");
    error.textContent = message;
    error.hidden = !message;
  },
};
