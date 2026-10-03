import { state } from "./state.js";
import { dom } from "../utils/dom.js";

export const patientSearch = {
  getFilters() {
    return {
      query: state.search,
      gender: state.gender,
      ageGroup: state.ageGroup,
      status: state.status,
    };
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

    state.search = "";
    state.gender = "";
    state.ageGroup = "";
    state.status = "";
    state.page = 1;
  },
};
