import { dom } from "../utils/dom.js";
import { dateUtils } from "../utils/dateUtils.js";

export const activitySummary = {
  render(summary = {}) {
    dom.get("totalActivities").textContent =
      Number(summary.total || 0).toLocaleString();
    dom.get("todayActivities").textContent =
      Number(summary.today || 0).toLocaleString();
    dom.get("activeUsers").textContent =
      Number(summary.activeUsers || 0).toLocaleString();
    dom.get("recentActivity").textContent =
      dateUtils.format(summary.recentActivity);
  },
};
