import { Loader } from "./loader.js";
import api from "../../../api/api.js";

let isLoggingOut = false;

export async function logout() {
  if (isLoggingOut) return;
  isLoggingOut = true;

  const loader = new Loader({ text: "Signing out..." });
  loader.show();

  try {
    const response = await api.post("auth/logout.php");
    const result = response.data;
    if (result?.status !== "success") {
      throw new Error(result?.message || "Unable to sign out.");
    }

    window.location.assign(
      result.redirect || `${window.location.origin}/login`,
    );
  } catch (error) {
    console.error("Unable to sign out:", error);
    window.alert(
      error.response?.data?.message ||
        error.message ||
        "Unable to sign out. Please try again.",
    );
    isLoggingOut = false;
  } finally {
    loader.hide();
  }
}

export function bindLogout() {
  document.querySelectorAll("[data-logout]").forEach((button) => {
    button.addEventListener("click", logout);
  });
}
