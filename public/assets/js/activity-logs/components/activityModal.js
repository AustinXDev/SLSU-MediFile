import { activityApi } from "./activityApi.js";
import { dom } from "../utils/dom.js";
import { dateUtils } from "../utils/dateUtils.js";
import { html } from "../utils/html.js";
import { formatLabel } from "../utils/formatLabel.js";

export const activityModal = {
  async open(id) {
    const record = await activityApi.getDetails(id);
    const fields = [
      ["Activity date/time", dateUtils.format(record.createdAt)],
      ["User", record.userName],
      ["Username", record.username],
      ["User role", record.userRole],
      ["Activity", formatLabel(record.action)],
      ["Module", formatLabel(record.module)],
      ["Description", record.description],
      ["Related record ID", record.recordId],
      ["IP address", record.ipAddress],
    ];

    dom.get("activityDetails").innerHTML = fields
      .filter(([, value]) => value !== null && value !== undefined && value !== "")
      .map(([label, value]) => `
        <div class="view-item ${label === "Description" ? "full-width" : ""}">
          <span>${html.escape(label)}</span>
          <strong class="${label === "Description" ? "activity-detail-description" : ""}">
            ${html.escape(value)}
          </strong>
        </div>`)
      .join("");
    dom.get("activityModalBackdrop").hidden = false;
    dom.get("closeActivityModal").focus();
  },

  close() {
    dom.get("activityModalBackdrop").hidden = true;
  },
};
