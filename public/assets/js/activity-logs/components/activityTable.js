import { dom } from "../utils/dom.js";
import { dateUtils } from "../utils/dateUtils.js";
import { html } from "../utils/html.js";
import { formatLabel } from "../utils/formatLabel.js";
import { state } from "./state.js";

export const activityTable = {
  render(records) {
    if (state.view === "patients") {
      this.renderPatients(records);
      return;
    }

    this.renderLogs(records);
  },

  renderLogs(records) {
    dom.get("activityTableHead").innerHTML = `
      <tr>
        <th>Date &amp; Time</th>
        <th>User</th>
        <th>Activity</th>
        <th>Module</th>
        <th>Description</th>
        <th>Record</th>
        <th>Details</th>
      </tr>`;

    dom.get("activityTableBody").innerHTML = records
      .map((record) => `
        <tr>
          <td>${html.escape(dateUtils.format(record.createdAt))}</td>
          <td>
            <span class="activity-primary-text">${html.escape(record.userName)}</span>
            <span class="activity-secondary-text">${html.escape(record.userRole || record.username || "")}</span>
          </td>
          <td><span class="activity-action-label">${html.escape(formatLabel(record.action))}</span></td>
          <td>${html.escape(formatLabel(record.module))}</td>
          <td>${html.escape(record.description || "—")}</td>
          <td>${record.recordId == null ? "—" : `#${html.escape(record.recordId)}`}</td>
          <td>${this.detailsButton(record.id, "View details")}</td>
        </tr>`)
      .join("");
  },

  renderPatients(records) {
    dom.get("activityTableHead").innerHTML = `
      <tr>
        <th>Patient</th>
        <th>Activity</th>
        <th>Description</th>
        <th>Date &amp; Time</th>
        <th>User</th>
        <th>Details</th>
      </tr>`;

    dom.get("activityTableBody").innerHTML = records
      .map((record) => {
        const name = [
          record.firstname,
          record.middlename,
          record.surname,
        ].filter(Boolean).join(" ");
        const patientId = record.studentId || `#${record.patientId}`;

        return `
          <tr>
            <td>
              <span class="activity-primary-text">${html.escape(name || "Patient")}</span>
              <span class="activity-secondary-text">Patient ID: ${html.escape(patientId)}</span>
            </td>
            <td><span class="activity-action-label">${html.escape(formatLabel(record.action))}</span></td>
            <td>${html.escape(record.description || "—")}</td>
            <td>${html.escape(dateUtils.format(record.createdAt))}</td>
            <td>${html.escape(record.userName)}</td>
            <td>${this.detailsButton(record.id, "View activity")}</td>
          </tr>`;
      })
      .join("");
  },

  detailsButton(id, label) {
    return `
      <button class="activity-view-button" type="button" data-details-id="${html.escape(id)}">
        ${label}
      </button>`;
  },

  renderPagination(pagination) {
    const {
      page = 1,
      total = 0,
      totalPages = 1,
      limit = state.pageSize,
    } = pagination;
    state.page = page;

    const first = total === 0 ? 0 : (page - 1) * limit + 1;
    const last = Math.min(page * limit, total);
    const noun = state.view === "patients" ? "patient activities" : "activities";
    dom.get("activityPaginationSummary").textContent =
      `Showing ${first}–${last} of ${total.toLocaleString()} ${noun}`;

    const start = Math.max(1, Math.min(page - 2, totalPages - 4));
    const end = Math.min(totalPages, start + 4);
    const buttons = [`
      <button type="button" data-page="${page - 1}" aria-label="Previous page" ${page <= 1 ? "disabled" : ""}>
        <i class="fa-solid fa-chevron-left"></i>
      </button>`];

    for (let number = start; number <= end; number += 1) {
      buttons.push(`
        <button type="button" data-page="${number}" class="${number === page ? "active" : ""}"
          aria-label="Page ${number}" ${number === page ? 'aria-current="page"' : ""}>
          ${number}
        </button>`);
    }

    buttons.push(`
      <button type="button" data-page="${page + 1}" aria-label="Next page" ${page >= totalPages ? "disabled" : ""}>
        <i class="fa-solid fa-chevron-right"></i>
      </button>`);
    dom.get("activityPagination").innerHTML = buttons.join("");
  },
};
