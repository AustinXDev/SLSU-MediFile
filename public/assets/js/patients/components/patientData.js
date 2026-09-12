import api from "../../../../api/api.js";
import { state } from "./state.js";

export const patientData = {
  fullName(patient) {
    return [patient.firstname, patient.middlename, patient.surname]
      .filter(Boolean)
      .join(" ")
      .replace(/\s+/g, " ")
      .trim();
  },

  nextId() {
    const highestId = state.patients.reduce(
      (highest, patient) => Math.max(highest, Number(patient.id)),
      0,
    );

    return `P-${new Date().getFullYear()}-${String(highestId + 1).padStart(
      4,
      "0",
    )}`;
  },

  find(id) {
    return state.patients.find(
      (patient) => patient.id == id || patient.patient_id == id,
    );
  },

  async load() {
    try {
      const response = await api.get("patients/get_patients.php");

      state.patients = response?.data?.data || response;

      return state.patients;
    } catch (error) {
      console.error("Failed to fetch patients:", error);
      throw error;
    }
  },

  async save(record) {
    if (state.editingId) {
      state.patients = state.patients.map((patient) =>
        patient.id === state.editingId ? record : patient,
      );

      return record;
    }

    const response = await api.post("patients/upsert.php", { record });

    return response;
  },

  remove(id) {
    state.patients = state.patients.filter((patient) => patient.id !== id);
  },
};
