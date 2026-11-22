import {
  changePassword,
  createBackup,
  deleteBackup,
  getBackups,
  getExportDatasets,
  getProfile,
  getSystemSettings,
  updateProfile,
  updateSystemSettings,
  uploadSchoolLogo,
} from "./settingsApi.js";
import { Loader } from "../components/loader.js";
import { bindLogout } from "../components/logout.js";

const config = window.SETTINGS_CONFIG;

function notify(title, message, type = "success") {
  if (typeof StatusModal === "function") {
    StatusModal.show(title, message, type);
  }
}

function apiMessage(error, fallback) {
  return error.response?.data?.message || error.message || fallback;
}

function setBusy(button, busy, idleText) {
  button.disabled = busy;
  button.innerHTML = busy
    ? '<i class="fa-solid fa-spinner fa-spin"></i> Please wait...'
    : idleText;
}

function bindProfile() {
  const form = document.getElementById("profileForm");
  const saveButton = document.getElementById("saveProfileButton");
  const originalButton = saveButton.innerHTML;

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const values = Object.fromEntries(new FormData(form).entries());
    setBusy(saveButton, true, originalButton);
    try {
      const result = await updateProfile(values);
      fillProfile(result.profile);
      notify(
        "Profile Updated",
        result.message || "Profile updated successfully.",
      );
    } catch (error) {
      console.error("Unable to update profile:", error);
      notify(
        "Unable to Save Changes",
        apiMessage(error, "Please try again."),
        "error",
      );
    } finally {
      setBusy(saveButton, false, originalButton);
    }
  });

  const passwordForm = document.getElementById("passwordForm");
  const toggle = document.getElementById("togglePasswordForm");
  const cancel = document.getElementById("cancelPasswordForm");
  const passwordButton = document.getElementById("changePasswordButton");
  const originalPasswordButton = passwordButton.innerHTML;

  toggle.addEventListener("click", () => {
    passwordForm.hidden = !passwordForm.hidden;
    if (!passwordForm.hidden) {
      passwordForm.querySelector('[name="current_password"]').focus();
    }
  });
  cancel.addEventListener("click", () => {
    passwordForm.reset();
    passwordForm.hidden = true;
  });
  passwordForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (!passwordForm.reportValidity()) return;
    const values = Object.fromEntries(new FormData(passwordForm).entries());
    if (values.new_password !== values.confirm_password) {
      notify(
        "Password Mismatch",
        "The new password and confirmation do not match.",
        "error",
      );
      return;
    }
    setBusy(passwordButton, true, originalPasswordButton);
    try {
      const result = await changePassword({
        current_password: values.current_password,
        new_password: values.new_password,
      });
      passwordForm.reset();
      passwordForm.hidden = true;
      notify(
        "Password Changed",
        result.message || "Password changed successfully.",
      );
    } catch (error) {
      console.error("Unable to change password:", error);
      notify(
        "Unable to Change Password",
        apiMessage(error, "Please try again."),
        "error",
      );
    } finally {
      setBusy(passwordButton, false, originalPasswordButton);
    }
  });
}

function fillProfile(profile) {
  document.getElementById("profileFirstName").value = profile.first_name || "";
  document.getElementById("profileLastName").value = profile.last_name || "";
  document.getElementById("profileUsername").value = profile.username || "";
  document.getElementById("profileEmail").value = profile.email || "";
  document
    .querySelectorAll(".admin-info strong, .profile-details strong")
    .forEach((element) => {
      element.textContent = profile.username || "";
    });
  document.getElementById("profileRole").textContent = profile.role || "—";
  const status = document.getElementById("profileStatus");
  status.textContent = profile.status || "—";
  status.classList.toggle(
    "settings-status-active",
    profile.status === "Active",
  );
  document.getElementById("profileLastLogin").textContent =
    profile.last_login_at
      ? new Date(
          String(profile.last_login_at).replace(" ", "T"),
        ).toLocaleString()
      : "No login recorded";
}

async function loadProfile() {
  try {
    const result = await getProfile();
    fillProfile(result.profile);
  } catch (error) {
    console.error("Unable to load profile:", error);
    notify(
      "Unable to Load Profile",
      apiMessage(error, "Please refresh the page."),
      "error",
    );
  }
}

async function initSystemSettings() {
  const form = document.getElementById("systemSettingsForm");
  if (!form) return;

  const saveButton = document.getElementById("saveSystemSettingsButton");
  const originalButton = saveButton.innerHTML;
  const logoPreview = document.getElementById("schoolLogoPreview");
  const logoInput = document.getElementById("schoolLogoInput");

  try {
    const result = await getSystemSettings();
    document.getElementById("applicationName").value =
      result.settings.application_name || "";
    document.getElementById("institutionName").value =
      result.settings.institution_name || "";
    document.getElementById("developerEmail").value =
      result.settings.developer_contact_email || "";
    if (result.settings.school_logo_path) {
      logoPreview.src = `${config.baseUrl}${result.settings.school_logo_path}`;
    }
  } catch (error) {
    console.error("Unable to load system settings:", error);
    notify(
      "Unable to Load Application Settings",
      apiMessage(error, "Please refresh the page."),
      "error",
    );
  }

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const values = Object.fromEntries(new FormData(form).entries());
    setBusy(saveButton, true, originalButton);
    try {
      const result = await updateSystemSettings(values);
      notify("Settings Saved", result.message || "Application settings saved.");
    } catch (error) {
      console.error("Unable to save system settings:", error);
      notify(
        "Unable to Save Settings",
        apiMessage(error, "Please try again."),
        "error",
      );
    } finally {
      setBusy(saveButton, false, originalButton);
    }
  });

  logoInput.addEventListener("change", async () => {
    const file = logoInput.files?.[0];
    if (!file) return;
    if (
      !["image/png", "image/jpeg", "image/webp"].includes(file.type) ||
      file.size > 2_000_000
    ) {
      logoInput.value = "";
      notify(
        "Invalid Logo",
        "Choose a PNG, JPEG, or WebP image no larger than 2 MB.",
        "error",
      );
      return;
    }
    try {
      const result = await uploadSchoolLogo(file);
      logoPreview.src = `${config.baseUrl}${result.settings.school_logo_path}`;
      notify(
        "Logo Updated",
        result.message || "School logo updated successfully.",
      );
    } catch (error) {
      console.error("Unable to upload school logo:", error);
      notify(
        "Unable to Upload Logo",
        apiMessage(error, "Please try again."),
        "error",
      );
    } finally {
      logoInput.value = "";
    }
  });
}

function formatSize(size) {
  const bytes = Number(size) || 0;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function renderBackups(backups) {
  const body = document.getElementById("backupTableBody");
  const summary = document.getElementById("backupSummary");
  body.replaceChildren();
  summary.textContent = `${backups.length} archive${backups.length === 1 ? "" : "s"}`;
  if (!backups.length) {
    const row = body.insertRow();
    const cell = row.insertCell();
    cell.colSpan = 6;
    cell.className = "settings-empty";
    cell.textContent = "No backups have been created yet.";
    return;
  }

  for (const backup of backups) {
    const row = body.insertRow();
    const date = row.insertCell();
    date.textContent = new Date(
      String(backup.created_at).replace(" ", "T"),
    ).toLocaleString();
    row.insertCell().textContent = backup.backup_type || "Full";
    row.insertCell().textContent = formatSize(backup.size_bytes);
    row.insertCell().textContent = backup.created_by || "Unavailable";
    row.insertCell().textContent = backup.status || "—";
    const actions = row.insertCell();
    actions.className = "settings-actions";

    const download = document.createElement("a");
    download.className = "action-button";
    download.href = `${window.APP_ENV.APP_URL}settings/backup-download.php?id=${encodeURIComponent(backup.backup_id)}`;
    download.setAttribute("aria-label", "Download backup");
    download.title = "Download backup";
    download.innerHTML = '<i class="fa-solid fa-download"></i>';
    actions.append(download);

    const remove = document.createElement("button");
    remove.className = "action-button delete";
    remove.type = "button";
    remove.setAttribute("aria-label", "Delete backup");
    remove.title = "Delete backup";
    remove.innerHTML = '<i class="fa-solid fa-trash"></i>';
    remove.addEventListener("click", () => removeBackup(backup.backup_id));
    actions.append(remove);
  }
}

async function loadBackups() {
  if (!config.canManageBackups) return;
  try {
    const result = await getBackups();
    renderBackups(result.backups);
  } catch (error) {
    console.error("Unable to load backups:", error);
    document.getElementById("backupSummary").textContent = apiMessage(
      error,
      "Unable to load backup history.",
    );
  }
}

async function removeBackup(id) {
  StatusModal.confirm(
    "Delete Backup",
    "Delete this backup archive? This action cannot be undone.",
    async () => {
      try {
        const result = await deleteBackup(id);
        await loadBackups();
        notify(
          "Backup Deleted",
          result.message || "Backup deleted successfully.",
        );
      } catch (error) {
        console.error("Unable to delete backup:", error);
        notify(
          "Unable to Delete Backup",
          apiMessage(error, "Please try again."),
          "error",
        );
      }
    },
  );
}

function bindBackupCreation() {
  const button = document.getElementById("createBackupButton");
  if (!button) return;
  const idleText = button.innerHTML;
  button.addEventListener("click", async () => {
    const loader = new Loader({ text: "Creating a database backup..." });
    setBusy(button, true, idleText);
    loader.show();
    try {
      const result = await createBackup();
      await loadBackups();
      notify(
        "Backup Created",
        result.message || "Backup created successfully.",
      );
    } catch (error) {
      console.error("Unable to create backup:", error);
      notify(
        "Unable to Create Backup",
        apiMessage(error, "Please try again."),
        "error",
      );
    } finally {
      loader.hide();
      setBusy(button, false, idleText);
    }
  });
}

async function initExport() {
  const form = document.getElementById("exportForm");
  if (!form) return;
  const datasetSelect = document.getElementById("exportDataset");
  try {
    const result = await getExportDatasets();
    datasetSelect.replaceChildren(new Option("Select a data set", ""));
    for (const dataset of result.datasets) {
      datasetSelect.add(new Option(dataset.label, dataset.key));
    }
  } catch (error) {
    console.error("Unable to load export datasets:", error);
    datasetSelect.replaceChildren(new Option("Unable to load data sets", ""));
    notify(
      "Unable to Load Export Data",
      apiMessage(error, "Please try again."),
      "error",
    );
  }

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    if (!form.reportValidity() || !datasetSelect.value) return;
    const url = new URL(`${window.APP_ENV.APP_URL}settings/export.php`);
    url.searchParams.set("dataset", datasetSelect.value);
    window.location.assign(url.toString());
  });
}

function bindSidebar() {
  window.openSidebar = () => {
    document.getElementById("sidebar")?.classList.add("sidebar-open");
    document.getElementById("sidebarOverlay")?.classList.add("active");
  };
  window.closeSidebar = () => {
    document.getElementById("sidebar")?.classList.remove("sidebar-open");
    document.getElementById("sidebarOverlay")?.classList.remove("active");
  };
}

async function init() {
  bindSidebar();
  bindLogout();
  bindProfile();
  await loadProfile();
  await Promise.all([initSystemSettings(), loadBackups(), initExport()]);
  bindBackupCreation();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", init);
} else {
  init();
}
