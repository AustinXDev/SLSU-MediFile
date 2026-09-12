import { dom } from "../utils/dom.js";
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

    this.requiredFields.forEach(([id, message]) => {
      if (!dom.value(id)) {
        this.setError(id, message);
        valid = false;
      }
    });

    if (
      dom.value("dateOfBirth") &&
      dateUtils.ageFromDob(dom.value("dateOfBirth")) === ""
    ) {
      this.setError("dateOfBirth", "Enter a valid date of birth.");
      valid = false;
    }

    return valid;
  },
};
