import { dom } from "./dom.js";

export const state = {
  page: 1,
  pageSize: 10,
  role: "",
  status: "",
  search: "",
  pendingDeleteId: null,
  pendingResetId: null,
  editingId: null,
  isLoading: false,
  loadRequestId: 0,
  roles: [],
  statuses: [],
};

export function setLoading(isLoading) {
  state.isLoading = isLoading;

  if (dom.tableLoading) {
    dom.tableLoading.hidden = false;
    dom.tableLoading.setAttribute("aria-busy", String(isLoading));
    dom.tableLoading.style.display = isLoading ? "flex" : "none";
    dom.tableLoading.hidden = !isLoading;
  }
}

export function applyFilterValues() {
  if (dom.roleFilter) dom.roleFilter.value = state.role;
  if (dom.statusFilter) dom.statusFilter.value = state.status;
}
