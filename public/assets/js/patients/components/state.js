import { dateUtils } from "../utils/dateUtils.js";
import { html } from "../utils/html.js";

export const state = {
  patients: [],
  page: 1,
  pageSize: 5,
  editingId: null,
  viewOnly: false,
};

export const serviceState = {
  services: [],

  add(service) {
    this.services.push(service);
  },

  remove(index) {
    this.services.splice(index, 1);
  },

  clear() {
    this.services = [];
  },

  getAll() {
    return this.services;
  },

  removeSaved(serviceId) {
    this.services = this.services.filter(
      (service) => Number(service.serviceId) !== Number(serviceId),
    );

    this.render();
  },

  render() {
    const tbody = document.getElementById("serviceHistoryBody");

    if (!tbody) return;

    const services = this.services;

    if (!services.length) {
      tbody.innerHTML = `
      <tr class="empty-service">
        <td colspan="5">
          No dental service records found.
        </td>
      </tr>
    `;
      return;
    }

    tbody.innerHTML = services
      .map((service, index) => {
        const signatureUrl = service.signaturePath
          ? `${window.API_URL}signature/dental-service-signature.php?path=${encodeURIComponent(
              service.signaturePath,
            )}`
          : null;

        const hasNewSignature = Boolean(service.patientSignature);
        const hasSavedSignature = Boolean(service.signaturePath);

        const isSaved = Boolean(service.serviceId);

        const signatureHtml = hasNewSignature
          ? `
            <span class="service-signature-captured">
              <i class="fa-solid fa-check-circle"></i>
              Signature captured
            </span>
          `
          : hasSavedSignature
            ? `
              <img
                src="${signatureUrl}"
                alt="Patient signature"
                class="service-signature-image"
                height="30"
                width="40"
              >
            `
            : `
              <span class="service-signature-missing">
                No signature
              </span>
            `;

        const deleteButton = isSaved
          ? `
            <button
              type="button"
              class="saved-service-delete"
              data-delete-service="${service.serviceId}"
              aria-label="Delete service"
            >
              <i class="fa-solid fa-trash"></i>
            </button>
          `
          : `
            <button
              type="button"
              class="temporary-service-remove"
              data-remove-service="${index}"
              aria-label="Remove service"
            >
              <i class="fa-solid fa-trash"></i>
            </button>
          `;

        return `
        <tr>
          <td>
            ${dateUtils.dateFormatter(service.serviceDate)}
          </td>

          <td>
            ${html.escape(service.serviceRendered)}
          </td>

          <td>
            ${signatureHtml}
          </td>

          <td></td>

          <td>
            ${deleteButton}
          </td>
        </tr>
      `;
      })
      .join("");
  },
};
