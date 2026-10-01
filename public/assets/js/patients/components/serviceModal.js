import { dom } from "../utils/dom.js";
import { serviceForm } from "./serviceForm.js";
import { signaturePad } from "./signaturePad.js";

export const serviceModal = {
  patientId: null,
  dentalId: null,

  open() {
    const modal = dom.get("addServiceModal");

    if (!modal) return;

    serviceForm.reset();

    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");

    requestAnimationFrame(() => {
      signaturePad.resize();
    });
  },

  close() {
    const modal = dom.get("addServiceModal");

    if (!modal) return;

    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");

    serviceForm.reset();

    this.patientId = null;
    this.dentalId = null;
  },
};
