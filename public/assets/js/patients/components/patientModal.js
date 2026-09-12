import { state } from "./state.js";
import { dom } from "../utils/dom.js";
import { dateUtils } from "../utils/dateUtils.js";
import { patientData } from "./patientData.js";
import { patientValidation } from "./patientValidation.js";

export const patientModal = {
  open(patient = null, viewOnly = false) {
    state.editingId = patient?.id || null;
    state.viewOnly = viewOnly;

    dom.get("patientForm").reset();
    patientValidation.clear();

    const record = patient || { id: patientData.nextId() };

    const fields = {
      patientId: record.id,
      patientIdDisplay: record.id,
      surName: record.surname,
      firstName: record.firstname,
      middleName: record.middlename,
      dateOfBirth: record.dob,
      age: dateUtils.ageFromDob(record.dob),
      gender: record.gender,
      civilStatus: record.civilStatus,
      religion: record.religion,
      nationality: record.nationality,
      department: record.department,
      position: record.position,
      contactNumber: record.contact,
      address: record.address,
      emergencyName: record.emergencyName,
      emergencyNumber: record.emergencyNumber,
      emergencyAddress: record.emergencyAddress,
    };

    Object.entries(fields).forEach(([id, value]) => dom.setValue(id, value));

    dom.get("patientModalTitle").textContent = viewOnly
      ? "Patient Record"
      : patient
        ? "Edit Patient Record"
        : "Add Patient Record";

    dom.get("patientModalDescription").textContent = viewOnly
      ? "Review the saved patient information."
      : "Enter the patient's information below.";

    dom.get("patientForm").classList.toggle("view-mode", viewOnly);
    dom.get("patientModal").hidden = false;

    document.body.style.overflow = "hidden";
  },

  close() {
    dom.get("patientModal").hidden = true;
    document.body.style.overflow = "";
  },
};
