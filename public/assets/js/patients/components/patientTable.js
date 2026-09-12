import { state } from "./state.js";
import { dom } from "../utils/dom.js";
import { dateUtils } from "../utils/dateUtils.js";
import { html } from "../utils/html.js";
import { patientData } from "./patientData.js";
import { patientSearch } from "./patientSearch.js";

export const patientTable = {
  initials(name) {
    return name
      .split(" ")
      .map((part) => part[0])
      .slice(0, 2)
      .join("")
      .toUpperCase();
  },

  row(patient) {
    console.log(patient);
    const name = patientData.fullName(patient);
    const escapedName = html.escape(name);
    const statusClass = patient.status === 1 ? "completed" : "inactive";

    return `<tr>
      <td>
        <span class="patient-id">${html.escape(patient.id)}</span>
      </td>

      <td>
        <div class="patient-name">
          <span class="patient-avatar">${this.initials(name)}</span>
          <div><strong>${escapedName}</strong><span> Patient record</span></div>
        </div>
      </td>

      <td style="font-size: 11px">
      ${dateUtils.ageFromDob(patient.dob)}
      </td>

      <td style="font-size: 11px">
        ${html.escape(patient.gender)}
      </td>

      <td style="font-size: 11px">
        ${html.escape(patient.contact)}
      </td>

      <td style="font-size: 11px">
        ${html.escape(dateUtils.dateFormatter(patient.createdAt) || "N/A")}
      </td>

      <td>
        <span style="font-size: 9px" class="status ${statusClass}">${patient.status === 1 ? html.escape("Active") : "Inactive"}</span>
      </td>

      <td>
        <div class="action-buttons">

          <button class="action-button" title="View patient record" aria-label="View ${escapedName}" data-action="view" data-id="${html.escape(patient.id)}">
            <i class="fa-solid fa-eye"></i>
          </button>

          <button class="action-button" title="Edit patient information" aria-label="Edit ${escapedName}" data-action="edit" data-id="${html.escape(patient.id)}">
            <i class="fa-solid fa-pen"></i>
          </button>

          <button class="action-button delete" title="Delete patient record" aria-label="Delete ${escapedName}" data-action="delete" data-id="${html.escape(patient.id)}">
            <i class="fa-solid fa-trash"></i>
          </button>
       </div>
      </td>
    </tr>`;
  },

  pagination(totalPages) {
    const pageButtons = Array.from(
      { length: totalPages },
      (_, index) =>
        `<button type="button" class="${
          state.page === index + 1 ? "active" : ""
        }" aria-label="Page ${index + 1}" data-page="${index + 1}">${
          index + 1
        }</button>`,
    ).join("");

    return `<button type="button" aria-label="Previous page" data-page="${
      state.page - 1
    }" ${state.page === 1 ? "disabled" : ""}><i class="fa-solid fa-chevron-left"></i></button>${pageButtons}<button type="button" aria-label="Next page" data-page="${
      state.page + 1
    }" ${state.page === totalPages ? "disabled" : ""}><i class="fa-solid fa-chevron-right"></i></button>`;
  },

  async render() {
    await patientData.load();

    const results = patientSearch.filterPatients();

    const totalPages = Math.max(1, Math.ceil(results.length / state.pageSize));

    state.page = Math.min(state.page, totalPages);

    const start = (state.page - 1) * state.pageSize;
    const pageRows = results.slice(start, start + state.pageSize);

    dom.get("patientsBody").innerHTML = pageRows
      .map((patient) => this.row(patient))
      .join("");

    dom.get("patientsTable").hidden = pageRows.length === 0;
    dom.get("emptyState").hidden = pageRows.length !== 0;

    dom.get("recordCount").textContent =
      `${results.length} patient${results.length === 1 ? "" : "s"}`;

    dom.get("resultSummary").textContent = patientSearch.hasActiveFilters()
      ? `${results.length} search result${results.length === 1 ? "" : "s"}`
      : "Showing patient records";

    dom.get("pagination").innerHTML = this.pagination(totalPages);
  },
};
