import api from "../../../api/api.js";

export async function getProfile() {
  return (await api.get("settings/profile.php")).data;
}

export async function updateProfile(profile) {
  return (await api.put("settings/profile.php", profile)).data;
}

export async function changePassword(passwords) {
  return (await api.post("settings/change-password.php", passwords)).data;
}

export async function getSystemSettings() {
  return (await api.get("settings/system.php")).data;
}

export async function updateSystemSettings(settings) {
  return (await api.put("settings/system.php", settings)).data;
}

export async function uploadSchoolLogo(file) {
  const form = new FormData();
  form.append("school_logo", file);
  return (
    await api.post("settings/logo.php", form, {
      headers: { "Content-Type": "multipart/form-data" },
    })
  ).data;
}

export async function getBackups() {
  return (await api.get("settings/backups.php")).data;
}

export async function createBackup() {
  return (
    await api.post("settings/backups.php", {}, { timeout: 0 })
  ).data;
}

export async function deleteBackup(backupId) {
  return (await api.post("settings/backup-delete.php", { backup_id: backupId })).data;
}

export async function getExportDatasets() {
  return (
    await api.get("settings/export.php", { params: { action: "datasets" } })
  ).data;
}
