import { activityData } from "./activityData.js";
import { activityFilters } from "./activityFilters.js";
import { activityModal } from "./activityModal.js";
import { debounce } from "../utils/debounce.js";
import { dom } from "../utils/dom.js";
import { state } from "./state.js";

const search = debounce(() => {
  state.page = 1;
  activityData.load();
}, 350);

export const activityEvents = {
  bind() {
    dom.get("activityFilters").addEventListener("submit", (event) => {
      event.preventDefault();
      this.applyFilters();
    });

    dom.get("resetFilters").addEventListener("click", () => {
      search.cancel();
      activityFilters.reset();
      state.page = 1;
      activityData.load();
    });

    dom.get("dateRange").addEventListener("change", () => {
      dom.get("customDates").hidden = dom.get("dateRange").value !== "custom";
      activityData.setError();
    });

    dom.get("activitySearch").addEventListener("input", search);

    //implement link
    dom.all("[data-activity-tab]").forEach((tab) => {
      tab.addEventListener("click", () => {
        activityFilters.setView(tab.dataset.activityTab);
        activityData.load();
        const url = new URL(window.location.href);

        if (url.searchParams.has("logType")) {
          url.searchParams.delete("logType");

          window.history.replaceState(
            {},
            document.title,
            url.pathname + url.search + url.hash,
          );
        }
      });
    });

    dom.get("activityPagination").addEventListener("click", (event) => {
      const button = event.target.closest("[data-page]");
      if (!button || button.disabled) return;
      const page = Number(button.dataset.page);
      if (!Number.isInteger(page) || page < 1) return;
      state.page = page;
      activityData.load();
    });

    dom.get("activityTableBody").addEventListener("click", (event) => {
      this.openDetails(event);
    });

    dom
      .get("closeActivityModal")
      .addEventListener("click", () => activityModal.close());
    dom
      .get("dismissActivityModal")
      .addEventListener("click", () => activityModal.close());
    dom.get("activityModalBackdrop").addEventListener("click", (event) => {
      if (event.target === dom.get("activityModalBackdrop"))
        activityModal.close();
    });
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && !dom.get("activityModalBackdrop").hidden) {
        activityModal.close();
      }
    });
  },

  applyFilters() {
    const isCustom = dom.get("dateRange").value === "custom";
    const dateFrom = dom.get("dateFrom").value;
    const dateTo = dom.get("dateTo").value;

    if (isCustom && (!dateFrom || !dateTo || dateFrom > dateTo)) {
      activityData.setError(
        "Choose a valid start and end date for the custom range.",
      );
      return;
    }

    activityData.setError();
    state.page = 1;
    activityData.load();
  },

  async openDetails(event) {
    const button = event.target.closest("[data-details-id]");
    if (!button) return;

    button.disabled = true;
    try {
      await activityModal.open(button.dataset.detailsId);
    } catch (error) {
      activityData.setError(
        error.message || "Unable to load activity details.",
      );
    } finally {
      button.disabled = false;
    }
  },

  startRefresh() {
    state.refreshTimer = window.setInterval(() => {
      if (!document.hidden && !state.loading) {
        activityData.load({ background: true });
      }
    }, 60_000);
  },
};
