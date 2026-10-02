import { dom } from "./dom.js";
import { debounce } from "../utils/debounce.js";
import { state, applyFilterValues } from "./state.js";
import { userData } from "./userData.js";
import {
  notify,
  openViewModal,
  openUserModal,
  closeModal,
} from "./userTable.js";
import { Loader } from "../../components/loader.js";

const passwordRequirements = [
  {
    key: "length",
    label: "At least 8 characters",
    message: "Password must be at least 8 characters long.",
    test: (password) => Array.from(password).length >= 8,
  },
  {
    key: "uppercase",
    label: "One uppercase letter",
    message: "Password must contain at least one uppercase letter.",
    test: (password) => /[A-Z]/.test(password),
  },
  {
    key: "lowercase",
    label: "One lowercase letter",
    message: "Password must contain at least one lowercase letter.",
    test: (password) => /[a-z]/.test(password),
  },
  {
    key: "number",
    label: "One number",
    message: "Password must contain at least one number.",
    test: (password) => /[0-9]/.test(password),
  },
  {
    key: "special",
    label: "One special character",
    message: "Password must contain at least one special character.",
    test: (password) => /[!@#$%^&*]/.test(password),
  },
];

function validateStrongPassword(password) {
  for (const requirement of passwordRequirements) {
    if (!requirement.test(password)) {
      throw new Error(requirement.message);
    }
  }
}

function updatePasswordRequirements(input, container) {
  if (!input || !container) return;

  const password = input.value.trim();
  for (const item of container.querySelectorAll("li[data-password-rule]")) {
    const requirement = passwordRequirements.find(
      ({ key }) => key === item.dataset.passwordRule,
    );
    if (!requirement) continue;

    const isMet = requirement.test(password);
    item.dataset.met = String(isMet);
    item.textContent = `${isMet ? "✓" : "○"} ${requirement.label}`;
  }
}

function validateUserForm(data) {
  const required = {
    first_name: "First name is required.",
    last_name: "Last name is required.",
    username: "Username is required.",
    email: "Email is required.",
    role: "Role is required.",
    status: "Status is required.",
  };

  for (const [field, message] of Object.entries(required)) {
    if (!String(data[field] ?? "").trim()) {
      throw new Error(message);
    }
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(data.email).trim())) {
    throw new Error("Please enter a valid email address.");
  }

  const password = String(data.password ?? "").trim();
  const confirmPassword = String(data.confirm_password ?? "").trim();

  if (password || confirmPassword) {
    if (!password) {
      throw new Error("Password is required.");
    }

    validateStrongPassword(password);

    if (!confirmPassword) {
      throw new Error("Password confirmation is required.");
    }

    if (password !== confirmPassword) {
      throw new Error("Passwords do not match.");
    }
  }
}

async function handleUserSubmit(event) {
  event.preventDefault();
  if (!dom.userForm) return;

  const formData = new FormData(dom.userForm);
  const payload = Object.fromEntries(formData.entries());
  const isEditing = Boolean(state.editingId);
  const load = new Loader({
    text: isEditing
      ? "Updating user account..."
      : "Creating new user account...",
  });

  try {
    validateUserForm(payload);

    if (!isEditing && !payload.password) {
      throw new Error("Password is required.");
    }

    if (!isEditing && !payload.confirm_password) {
      throw new Error("Password confirmation is required.");
    }

    const body = { ...payload };

    if (isEditing) {
      body.id = state.editingId;
      if (!body.password) {
        delete body.password;
      }
      delete body.confirm_password;
    }

    StatusModal.confirm(
      isEditing ? "Confirm User Update" : "Confirm New User",
      "Are you sure you want to" +
        (isEditing
          ? " update this user account?"
          : " create this new user account?"),
      async () => {
        load.show();

        try {
          const response = isEditing
            ? await userData.updateAccount(body)
            : await userData.createAccount(body);

          if (response?.status === "error") {
            StatusModal.show(
              "Failed to Save",
              isEditing ? "Failed to Update" : "Failed to Create",
              response.message ||
                "An error occurred while saving the user account.",
              "error",
            );
            return;
          }

          closeModal("userModalBackdrop");
          StatusModal.show(
            isEditing ? "User Updated" : "User Created",
            response?.message || "User account saved successfully.",
            "success",
          );

          state.page = 1;
          await userData.loadAccounts();
        } catch (error) {
          console.error("User account save failed.");
          StatusModal.show(
            "Save Failed",
            error.message || "Unable to save the user account.",
            "error",
          );
        } finally {
          load.hide();
        }
      },
    );
  } catch (error) {
    StatusModal.show(
      "Validation Error",
      error.message || "Please check the form for errors.",
      "error",
    );
  }
}

async function handlePasswordSubmit(event) {
  event.preventDefault();
  if (!dom.passwordForm) return;

  const formData = new FormData(dom.passwordForm);
  const newPassword = String(formData.get("new_password") || "").trim();
  const confirmPassword = String(
    formData.get("confirm_new_password") || "",
  ).trim();

  try {
    validateStrongPassword(newPassword);

    if (newPassword !== confirmPassword) {
      throw new Error("Passwords do not match.");
    }
  } catch (error) {
    notify(error.message, "error");
    return;
  }

  try {
    const response = await userData.resetPassword(
      state.pendingResetId,
      newPassword,
    );

    closeModal("passwordModalBackdrop");
    StatusModal.show(
      "Password Reset",
      response?.message || "Password reset successfully.",
      "success",
    );
  } catch (error) {
    StatusModal.show(
      "Reset Failed",
      error.message || "Failed to reset password.",
      "error",
    );
  }
}

async function handleTableAction(event) {
  const button = event.target.closest("button[data-action]");
  if (!button) return;

  const action = button.dataset.action;
  const id = Number(button.dataset.id);

  if (!id) return;

  try {
    if (action === "view") {
      const load = new Loader({ text: "Loading user data..." });
      load.show();

      try {
        const account = await userData.getAccount(id);
        openViewModal(account || {});
        return;
      } catch (error) {
        StatusModal.show(
          "Error",
          "Failed to load user account details.",
          "error",
        );
        console.error(error);
        return;
      } finally {
        load.hide();
      }
    }

    if (action === "edit") {
      const load = new Loader({ text: "Loading user data..." });
      load.show();

      try {
        const account = await userData.getAccount(id);
        openUserModal("edit", account || {});
        return;
      } catch (error) {
        StatusModal.show(
          "Error",
          "Failed to load user account details.",
          "error",
        );
        console.error(error);
        return;
      } finally {
        load.hide();
      }
    }

    if (action === "delete") {
      state.pendingDeleteId = id;

      if (!state.pendingDeleteId) {
        throw new Error("No user ID specified for deletion.");
      }

      const load = new Loader({ text: "Deleting user account..." });

      StatusModal.confirm(
        "Delete user account?",
        "This will remove the user account from the list.",
        async () => {
          load.show();

          try {
            const response = await userData.deleteAccount(
              state.pendingDeleteId,
            );

            if (response?.status === "error") {
              showStatus(
                "Delete Failed",
                response.message || "Failed to delete user account.",
                "error",
              );
            } else {
              StatusModal.show(
                "User Deleted",
                response?.message || "User account deleted successfully.",
                "success",
              );
              await userData.loadAccounts();
            }
          } catch (error) {
            console.error(error);
            StatusModal.show(
              "Delete Failed",
              error.message || "Failed to delete user account.",
              "error",
            );
          } finally {
            load.hide();
          }
        },
      );
    }
  } catch (error) {
    StatusModal.show(
      "Error",
      error.message || "An unexpected error occurred.",
      "error",
    );
  }
}

export const userEvents = {
  bind() {
    if (dom.userSearch) {
      const updateSearch = debounce((text) => {
        state.search = text;
        state.page = 1;
        userData.loadAccounts();
      });
      dom.userSearch.addEventListener("input", (event) => {
        updateSearch(event.target.value.trim());
      });
    }

    if (dom.roleFilter) {
      dom.roleFilter.addEventListener("change", (event) => {
        state.role = event.target.value;
        state.page = 1;
        userData.loadAccounts();
      });
    }

    if (dom.statusFilter) {
      dom.statusFilter.addEventListener("change", (event) => {
        state.status = event.target.value;
        state.page = 1;
        userData.loadAccounts();
      });
    }

    if (dom.clearFiltersButton) {
      dom.clearFiltersButton.addEventListener("click", () => {
        state.search = "";
        state.role = "";
        state.status = "";
        state.page = 1;

        if (dom.userSearch) dom.userSearch.value = "";

        applyFilterValues();
        userData.loadAccounts();
      });
    }

    if (dom.addUserButton) {
      dom.addUserButton.addEventListener("click", () => {
        openUserModal("create");
      });
    }

    if (dom.userForm) {
      dom.userForm.addEventListener("submit", handleUserSubmit);

      const passwordInput = dom.userForm.querySelector('[name="password"]');
      const requirements = dom.userForm.querySelector(
        "#passwordSection .helper-box",
      );
      if (passwordInput && requirements) {
        const updateFeedback = () =>
          updatePasswordRequirements(passwordInput, requirements);
        passwordInput.addEventListener("input", updateFeedback);
        dom.userForm.addEventListener("reset", () => {
          window.setTimeout(updateFeedback, 0);
        });
        updateFeedback();
      }
    }
    if (dom.passwordForm) {
      dom.passwordForm.addEventListener("submit", handlePasswordSubmit);

      const passwordInput = dom.passwordForm.querySelector(
        '[name="new_password"]',
      );
      const requirements = dom.passwordForm.querySelector(".helper-box");
      if (passwordInput && requirements) {
        const updateFeedback = () =>
          updatePasswordRequirements(passwordInput, requirements);
        passwordInput.addEventListener("input", updateFeedback);
        updateFeedback();
      }
    }
    if (dom.usersBody) {
      dom.usersBody.addEventListener("click", handleTableAction);
    }

    document.querySelectorAll("[data-close]").forEach((button) => {
      button.addEventListener("click", () => {
        const target = button.dataset.close;
        const modal = document.getElementById(
          target === "userModal"
            ? "userModalBackdrop"
            : target === "viewModal"
              ? "viewModalBackdrop"
              : target === "passwordModal"
                ? "passwordModalBackdrop"
                : "confirmModalBackdrop",
        );

        if (modal) modal.hidden = true;
      });
    });

    if (dom.pagination) {
      dom.pagination.addEventListener("click", (event) => {
        const button = event.target.closest("button[data-page]");
        if (!button) return;

        const nextPage = Number(button.dataset.page);
        if (!nextPage || nextPage < 1) return;

        state.page = nextPage;
        userData.loadAccounts();
      });
    }
  },
};

export async function loadAccounts() {
  return userData.loadAccounts();
}

export function attachUserEvents() {
  userEvents.bind();
}
