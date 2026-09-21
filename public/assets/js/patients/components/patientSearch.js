import { state } from "./state.js";
import { dom } from "../utils/dom.js";
import { dateUtils } from "../utils/dateUtils.js";
import { patientData } from "./patientData.js";

export const patientSearch = {
  getFilters() {
    return {
      query: dom.value("patientSearch").toLowerCase(),
      gender: dom.get("genderFilter")?.value || "",
      ageGroup: dom.get("ageFilter")?.value || "",
      status: dom.get("statusFilter")?.value || "",
    };
  },

  matchesAgeGroup(age, ageGroup) {
    return (
      !ageGroup ||
      (ageGroup === "child" && age < 18) ||
      (ageGroup === "adult" && age >= 18 && age < 60) ||
      (ageGroup === "senior" && age >= 60)
    );
  },

  filterPatients() {
    const filters = this.getFilters();

    return state.patients.filter((patient) => {
      const name = patientData.fullName(patient).toLowerCase();

      const age = dateUtils.ageFromDob(patient.dob);

      const matchesQuery =
        !filters.query ||
        [name, patient.contact.toLowerCase()].some((value) =>
          value.includes(filters.query),
        );

      return (
        matchesQuery &&
        (!filters.gender || patient.gender === filters.gender) &&
        (!filters.status || patient.status === filters.status) &&
        this.matchesAgeGroup(age, filters.ageGroup)
      );
    });
  },

  hasActiveFilters() {
    const filters = this.getFilters();

    return Boolean(
      filters.query || filters.gender || filters.ageGroup || filters.status,
    );
  },

  clear() {
    ["patientSearch", "headerSearch"].forEach((id) => dom.setValue(id, ""));

    ["genderFilter", "ageFilter", "statusFilter"].forEach((id) =>
      dom.setValue(id, ""),
    );

    state.page = 1;
  },
};
