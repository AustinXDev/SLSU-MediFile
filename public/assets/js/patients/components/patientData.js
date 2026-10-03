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
    return `P-${new Date().getFullYear()}-${String(state.highestPatientId + 1).padStart(
      4,
      "0",
    )}`;
  },

  find(id) {
    return state.patients.find(
      (patient) => patient.id == id || patient.patient_id == id,
    );
  },

  async loadPatients() {
    const parsedPage = Number(state.page);
    state.page = Number.isInteger(parsedPage) && parsedPage > 0 ? parsedPage : 1;

    const parsedLimit = Number(state.pageSize);
    state.pageSize = Number.isInteger(parsedLimit)
      ? Math.max(1, Math.min(100, parsedLimit))
      : 5;

    try {
      const response = await api.get("patients/get_patients.php", {
        params: {
          page: state.page,
          limit: state.pageSize,
          search: state.search,
          gender: state.gender,
          ageGroup: state.ageGroup,
          status: state.status,
        },
      });

      const result = response?.data?.data || {};
      const pagination = result.pagination || {};

      state.patients = Array.isArray(result.patients) ? result.patients : [];
      state.page = Number(pagination.page) || state.page;
      state.pageSize = Number(pagination.limit) || state.pageSize;
      state.total = Number(pagination.total) || 0;
      state.totalPages = Math.max(1, Number(pagination.totalPages) || 1);
      state.highestPatientId = Number(result.maxPatientId) || 0;

      return result;
    } catch (error) {
      console.error("Failed to fetch patients:", error);
      throw error;
    }
  },

  async load() {
    return this.loadPatients();
  },

  async save(record) {
    const payload = state.editingId
      ? { ...record, id: state.editingId }
      : record;

    const response = await api.post("patients/upsert.php", { payload });

    return response;
  },

  async remove(id) {
    const response = await api.post("patients/delete.php", { patientId: id });

    return response;
  },
};
