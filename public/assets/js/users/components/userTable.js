import { dom } from "./dom.js";
import { state } from "./state.js";

export const escapeHtml = (value) =>
  String(value ?? "").replace(
    /[&<>\"']/g,
    (char) =>
      ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;",
      })[char],
  );

export const formatDate = (value) => {
  if (!value || value === "0000-00-00" || value === "0000-00-00 00:00:00") {
    return "Not specified";
  }

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "Not specified";

  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

export const notify = (message, type = "success") => {
  if (!dom.notification) return;

  dom.notification.textContent = message;
  dom.notification.classList.add("show");
  dom.notification.style.background = type === "error" ? "#b42318" : "#047857";

  window.clearTimeout(notify.timer);
  notify.timer = window.setTimeout(() => {
    dom.notification.classList.remove("show");
  }, 2600);
};

export function populateFilterData(data = {}) {
  state.roles = Array.isArray(data.roles) ? data.roles : [];
  state.statuses = Array.isArray(data.statuses) ? data.statuses : [];

  if (dom.roleFilter) {
    dom.roleFilter.innerHTML = `<option value="">All Roles</option>${state.roles
      .map(
        (value) =>
          `<option value="${escapeHtml(value)}">${escapeHtml(value)}</option>`,
      )
      .join("")}`;
  }

  if (dom.statusFilter) {
    dom.statusFilter.innerHTML = `<option value="">All Statuses</option>${state.statuses
      .map(
        (value) =>
          `<option value="${escapeHtml(value)}">${escapeHtml(value)}</option>`,
      )
      .join("")}`;
  }

  if (dom.roleSelect) {
    dom.roleSelect.innerHTML = `<option value="">Select role</option>${state.roles
      .map(
        (value) =>
          `<option value="${escapeHtml(value)}">${escapeHtml(value)}</option>`,
      )
      .join("")}`;
  }

  if (dom.statusSelect) {
    dom.statusSelect.innerHTML = `<option value="">Select status</option>${state.statuses
      .map(
        (value) =>
          `<option value="${escapeHtml(value)}">${escapeHtml(value)}</option>`,
      )
      .join("")}`;
  }

  if (dom.roleFilter) dom.roleFilter.value = state.role;
  if (dom.statusFilter) dom.statusFilter.value = state.status;
}

export function renderTable(accounts = []) {
  if (!dom.usersBody) return;

  const normalizedAccounts = Array.isArray(accounts) ? accounts : [];

  if (!normalizedAccounts.length || normalizedAccounts.length === 0) {
    dom.usersBody.innerHTML = "";
    if (dom.usersTable) dom.usersTable.hidden = true;
    if (dom.emptyState) dom.emptyState.hidden = false;
    return;
  }

  if (dom.usersTable) dom.usersTable.hidden = false;
  if (dom.emptyState) dom.emptyState.hidden = true;

  dom.usersBody.innerHTML = normalizedAccounts
    .map((account) => {
      const fullName =
        [account.first_name, account.last_name].filter(Boolean).join(" ") ||
        "Unknown account";

      const initials = fullName
        .split(" ")
        .map((part) => part.charAt(0))
        .slice(0, 2)
        .join("")
        .toUpperCase();

      const statusClass =
        account.status === "Active"
          ? "status-active"
          : account.status === "Inactive"
            ? "status-inactive"
            : "status-suspended";

      return `
        <tr>
          <td>
            <div class="user-name">
              <span class="user-avatar">${escapeHtml(initials || "NA")}</span>
              <div>
                <strong>${escapeHtml(fullName)}</strong>
                <small>Staff account</small>
              </div>
            </div>
          </td>
          <td>
            <div class="username-email">
              <a href="mailto:${escapeHtml(account.email || "")}">${escapeHtml(account.username || "")}</a>
              <span>${escapeHtml(account.email || "")}</span>
            </div>
          </td>
          <td>${escapeHtml(account.role || "")}</td>
          <td><span class="status-badge ${escapeHtml(statusClass)}">${escapeHtml(account.status || "Active")}</span></td>
          <td>${escapeHtml(formatDate(account.created_at))}</td>
          <td>${escapeHtml(formatDate(account.updated_at))}</td>
          <td>
            <div class="user-actions">
              <button class="action-button" type="button" data-action="view" data-id="${escapeHtml(account.admin_id)}">View</button>
              <button class="action-button" type="button" data-action="edit" data-id="${escapeHtml(account.admin_id)}">Edit</button>
              <button class="action-button delete" type="button" data-action="delete" data-id="${escapeHtml(account.admin_id)}">Delete</button>
            </div>
          </td>
        </tr>
      `;
    })
    .join("");
}

export function renderPagination(
  pagination = { page: 1, totalPages: 1, total: 0 },
) {
  if (!dom.pagination) return;

  const totalPages = Math.max(1, Number(pagination.totalPages || 1));
  const currentPage = Math.min(Number(pagination.page || 1), totalPages);

  const pages = [];
  for (let page = 1; page <= totalPages; page += 1) {
    pages.push(`
      <button type="button" class="${page === currentPage ? "active" : ""}" data-page="${page}" ${page === currentPage ? "disabled" : ""}>${page}</button>
    `);
  }

  const prevDisabled = currentPage <= 1;
  const nextDisabled = currentPage >= totalPages;

  dom.pagination.innerHTML = `
    <button type="button" data-page="${currentPage - 1}" ${prevDisabled ? "disabled" : ""}>Previous</button>
    ${pages.join("")}
    <button type="button" data-page="${currentPage + 1}" ${nextDisabled ? "disabled" : ""}>Next</button>
  `;
}

export function openViewModal(account) {
  if (!dom.viewModalBackdrop || !dom.viewAccountDetails) return;

  const fullName =
    [account.first_name, account.last_name].filter(Boolean).join(" ") ||
    "Unknown account";

  dom.viewAccountDetails.innerHTML = `
    <div class="view-item"><span>Full Name</span><strong>${escapeHtml(fullName)}</strong></div>
    <div class="view-item"><span>Username</span><strong>${escapeHtml(account.username || "")}</strong></div>
    <div class="view-item"><span>Email</span><strong>${escapeHtml(account.email || "")}</strong></div>
    <div class="view-item"><span>Role</span><strong>${escapeHtml(account.role || "")}</strong></div>
    <div class="view-item"><span>Status</span><strong>${escapeHtml(account.status || "")}</strong></div>
    <div class="view-item"><span>Created Date</span><strong>${escapeHtml(formatDate(account.created_at))}</strong></div>
    <div class="view-item"><span>Updated Date</span><strong>${escapeHtml(formatDate(account.updated_at))}</strong></div>
  `;

  dom.viewModalBackdrop.hidden = false;
}

export function openUserModal(mode, account = null) {
  if (!dom.userModalBackdrop || !dom.userForm) return;

  state.editingId =
    mode === "edit" && account ? Number(account.admin_id) : null;
  dom.userModalBackdrop.hidden = false;
  dom.userForm.reset();

  if (dom.roleSelect) dom.roleSelect.value = "";
  if (dom.statusSelect) dom.statusSelect.value = "";
  if (dom.passwordSection) dom.passwordSection.hidden = false;

  if (dom.saveUserButton) {
    dom.saveUserButton.textContent =
      mode === "edit" ? "Save Changes" : "Create User";
  }

  if (dom.userModalTitle) {
    dom.userModalTitle.textContent =
      mode === "edit" ? "Edit User Account" : "Create User Account";
  }

  if (mode === "edit" && account) {
    const firstName = document.getElementById("first_name");
    const lastName = document.getElementById("last_name");
    const username = document.getElementById("username");
    const email = document.getElementById("email");

    if (firstName) firstName.value = account.first_name || "";
    if (lastName) lastName.value = account.last_name || "";
    if (username) username.value = account.username || "";
    if (email) email.value = account.email || "";
    if (dom.roleSelect) dom.roleSelect.value = account.role || "";
    if (dom.statusSelect) dom.statusSelect.value = account.status || "";
  }
}

export function closeModal(id) {
  const element = document.getElementById(id);
  if (element) element.hidden = true;
}
