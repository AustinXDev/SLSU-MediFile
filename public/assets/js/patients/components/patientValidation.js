import { dom, selected } from "../utils/dom.js";
import { dateUtils } from "../utils/dateUtils.js";

export const patientValidation = {
  requiredFields: [
    ["firstName", "First name is required."],
    ["surName", "Surname is required."],
    ["middleName", "Middle name is required."],
    ["dateOfBirth", "Date of birth is required."],
    ["gender", "Please select a gender."],
    ["civilStatus", "Please select a civil status."],
    ["religion", "Religion is required."],
    ["nationality", "Nationality is required."],
    ["department", "College or department is required."],
    ["position", "Job position or course is required."],
    ["contactNumber", "Contact number is required."],
    ["emergencyName", "Emergency contact name is required."],
    ["emergencyNumber", "Emergency contact number is required."],
    ["emergencyAddress", "Emergency address is required."],
  ],

  clear() {
    document.querySelectorAll(".form-field").forEach((field) => {
      field.classList.remove("has-error");

      const error = field.querySelector(".field-error");
      if (error) error.textContent = "";
    });
  },

  setError(id, message) {
    const field = dom.get(id)?.closest(".form-field");
    if (!field) return;

    field.classList.add("has-error");

    const error = field.querySelector(".field-error");
    if (error) error.textContent = message;
  },

  formIsValid() {
    this.clear();

    let valid = true;
    let firstInvalidId = null;

    this.requiredFields.forEach(([id, message]) => {
      if (!dom.value(id)) {
        this.setError(id, message);
        valid = false;

        if (!firstInvalidId) {
          firstInvalidId = id;
        }
      }
    });

    if (
      dom.value("dateOfBirth") &&
      dateUtils.ageFromDob(dom.value("dateOfBirth")) === ""
    ) {
      this.setError("dateOfBirth", "Enter a valid date of birth.");
      valid = false;

      if (!firstInvalidId) {
        firstInvalidId = "dateOfBirth";
      }
    }

    if (firstInvalidId) {
      const input = dom.get(firstInvalidId);

      if (input) {
        input.focus();
      }
    }

    return valid;
  },
};

export const dentalRecordValidation = {
  validInput: [
    "C",
    "C1",
    "C2",
    "X",
    "RF",
    "AM",
    "S",
    "GC",
    "AB",
    "P",
    "U",
    "GI",
    "M",
  ],

  setError(id) {
    const field = dom.get(id)?.closest(".dental-field");

    if (!field) return;

    field.classList.add("has-error");
  },

  clearError(id) {
    const field = dom.get(id)?.closest(".dental-field");

    if (!field) return;

    field.classList.remove("has-error");
  },

  validate(id, value) {
    const normalizedValue = value.trim().toUpperCase();

    // Empty is allowed
    if (!normalizedValue) {
      this.clearError(id);
      return true;
    }

    const valid = this.validInput.includes(normalizedValue);

    if (valid) {
      this.clearError(id);
    } else {
      this.setError(id);
    }

    return valid;
  },

  formIsValid() {
    let valid = true;
    let firstInvalidId = null;

    selected.get(".dental-field")?.forEach((field) => {
      if (!field) return;

      if (!this.validate(field.id, field.value)) {
        valid = false;

        if (!firstInvalidId) {
          firstInvalidId = field.id;
        }
      }
    });

    if (firstInvalidId) {
      const input = dom.get(firstInvalidId);

      if (input) {
        input.focus();
      }
    }

    return valid;
  },
};
