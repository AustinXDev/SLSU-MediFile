import { dom } from "../utils/dom.js";
import { dateUtils } from "../utils/dateUtils.js";
import { formatLabel } from "../utils/formatLabel.js";
import { state } from "./state.js";

export const activityFilters = {
  params() {
    const params = new URLSearchParams({
      view: state.view,
      page: String(state.page),
      limit: String(state.pageSize),
      search: dom.get("activitySearch").value.trim(),
      action: dom.get("activityFilter").value,
      module: dom.get("moduleFilter").value,
    });

    const { dateFrom, dateTo } = dateUtils.filters(
      dom.get("dateRange").value,
      dom.get("dateFrom").value,
      dom.get("dateTo").value,
    );

    if (dateFrom) params.set("date_from", dateFrom);
    if (dateTo) params.set("date_to", dateTo);
    return params;
  },

  setOptions(filters = {}) {
    [
      [dom.get("activityFilter"), "All Activities", filters.actions || []],
      [dom.get("moduleFilter"), "All Modules", filters.modules || []],
    ].forEach(([select, placeholder, values]) => {
      const current = select.value;
      select.replaceChildren(new Option(placeholder, ""));
      values.forEach((value) => {
        select.add(new Option(formatLabel(value), value));
      });
      select.value = values.includes(current) ? current : "";
    });
  },

  reset() {
    dom.get("activityFilters").reset();
    dom.get("customDates").hidden = true;

    if (state.view === "patients") {
      dom.get("moduleFilter").value = "patients";
      state.savedModule = "";
    }
  },

  setView(view) {
    const nextView = view === "patients" ? "patients" : "logs";
    const moduleFilter = dom.get("moduleFilter");

    if (nextView === "patients" && state.view !== "patients") {
      state.savedModule = moduleFilter.value;
      moduleFilter.value = "patients";
      moduleFilter.disabled = true;
    } else if (nextView === "logs" && state.view !== "logs") {
      moduleFilter.disabled = false;
      if (state.savedModule !== null) {
        moduleFilter.value = state.savedModule;
        state.savedModule = null;
      }
    }

    state.view = nextView;
    state.page = 1;
    dom.all("[data-activity-tab]").forEach((tab) => {
      const selected = tab.dataset.activityTab === state.view;
      tab.classList.toggle("active", selected);
      tab.setAttribute("aria-selected", String(selected));
      tab.tabIndex = selected ? 0 : -1;
    });
    dom
      .get("activityPanel")
      .setAttribute(
        "aria-labelledby",
        state.view === "patients" ? "patientsTab" : "logsTab",
      );
  },
};
