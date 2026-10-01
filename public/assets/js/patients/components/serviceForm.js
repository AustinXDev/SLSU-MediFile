import { dom } from "../utils/dom.js";
import { signaturePad } from "./signaturePad.js";

export const serviceForm = {
  buildRecord() {
    return {
      serviceDate: dom.value("serviceDate"),
      serviceRendered: dom.value("serviceRendered"),

      patientSignature: signaturePad.getData(),
    };
  },

  validate(record) {
    const errors = {};

    if (!record.serviceDate) {
      errors.serviceDate = "Service date is required.";
    }

    if (!record.serviceRendered?.trim()) {
      errors.serviceRendered = "Service rendered is required.";
    }

    return errors;
  },

  reset() {
    const dateInput = dom.get("serviceDate");
    const serviceInput = dom.get("serviceRendered");

    if (dateInput) {
      dateInput.value = "";
    }

    if (serviceInput) {
      serviceInput.value = "";
    }

    signaturePad.reset();
  },
};
