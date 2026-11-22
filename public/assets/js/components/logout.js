import { Loader } from "./loader.js";
import api from "../../../api/api.js";
import { AutoLogout } from "./autoLogout.js";

let isLoggingOut = false;
const boundLogoutButtons = new WeakSet();

function defaultLoginUrl() {
  const baseUrl =
    window.APP_ENV?.BASE_URL ??
    window.SETTINGS_CONFIG?.baseUrl ??
    `${window.location.origin}/`;
  return new URL("login", baseUrl).toString();
}

export async function logout({
  automatic = false,
  logoutUrl = "auth/logout.php",
  loginUrl = defaultLoginUrl(),
} = {}) {
  if (isLoggingOut) {
    if (automatic) window.location.assign(loginUrl);
    return;
  }
  isLoggingOut = true;

  const loader = automatic ? null : new Loader({ text: "Signing out..." });
  loader?.show();

  try {
    const response = await api.post(logoutUrl);
    const result = response.data;
    if (result?.status !== "success") {
      throw new Error(result?.message || "Unable to sign out.");
    }

    window.location.assign(loginUrl || result.redirect || defaultLoginUrl());
  } catch (error) {
    console.error("Unable to sign out:", error);
    if (automatic) {
      window.location.assign(loginUrl);
    } else {
      window.alert(
        error.response?.data?.message ||
          error.message ||
          "Unable to sign out. Please try again.",
      );
      isLoggingOut = false;
    }
  } finally {
    loader?.hide();
  }
}

export function bindLogout() {
  const configuration = window.AUTO_LOGOUT_CONFIG || {};
  AutoLogout.init({
    ...configuration,
    onLogout: () => logout({ automatic: true }),
  });

  document.querySelectorAll("[data-logout]").forEach((button) => {
    if (boundLogoutButtons.has(button)) return;
    boundLogoutButtons.add(button);
    button.addEventListener("click", () => logout());
  });
}
