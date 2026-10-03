import {
  requestPasswordReset,
  resetPassword,
  validateResetToken,
} from "./passwordResetApi.js";

const config = window.PASSWORD_RESET_CONFIG;
const message = document.getElementById("resetMessage");

function showMessage(text, type = "error") {
  message.textContent = text;
  message.className = `auth-reset-message is-${type}`;
  message.hidden = false;
}

function clearMessage() {
  message.textContent = "";
  message.className = "auth-reset-message";
  message.hidden = true;
}

function setBusy(button, busy, idleText) {
  button.disabled = busy;
  button.textContent = busy ? "Please wait..." : idleText;
}

function errorMessage(error, fallback) {
  return error.response?.data?.message || error.message || fallback;
}

function initRequestForm() {
  const form = document.getElementById("requestResetForm");
  const emailInput = document.getElementById("resetEmail");
  const emailField = document.getElementById("emailField");
  const button = document.getElementById("requestResetButton");
  const retryButton = document.getElementById("requestAnotherLink");
  const heading = document.getElementById("resetHeading");
  const description = document.getElementById("resetDescription");
  const idleText = button.textContent.trim();

  emailInput.addEventListener("input", () => {
    emailField.classList.remove("has-error");
    clearMessage();
  });

  retryButton.addEventListener("click", () => {
    retryButton.hidden = true;
    form.hidden = false;
    emailInput.focus();
    clearMessage();
  });

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    const email = emailInput.value.trim();

    if (!emailInput.validity.valid || !email) {
      emailField.classList.add("has-error");
      emailInput.focus();
      return;
    }

    clearMessage();
    emailField.classList.remove("has-error");
    setBusy(button, true, idleText);

    try {
      const result = await requestPasswordReset(email);
      showMessage(
        result.message ||
          "If an account exists for that email address, a password reset link has been sent.",
        "success",
      );
      heading.textContent = "Check your email";
      description.textContent =
        "If an account exists for that email address, you will receive a password reset link shortly.";
      form.hidden = true;
      retryButton.hidden = false;
    } catch (error) {
      console.error("Password reset request failed:", error);
      showMessage(
        errorMessage(error, "Unable to process your request. Please try again."),
      );
    } finally {
      setBusy(button, false, idleText);
    }
  });
}

function getPasswordRequirements(password) {
  return {
    length: Array.from(password).length >= 8,
    uppercase: /[A-Z]/.test(password),
    lowercase: /[a-z]/.test(password),
    number: /[0-9]/.test(password),
    special: /[!@#$%^&*]/.test(password),
  };
}

function updatePasswordRequirements(password) {
  const requirements = getPasswordRequirements(password);
  document.querySelectorAll("[data-requirement]").forEach((item) => {
    const met = requirements[item.dataset.requirement];
    item.classList.toggle("is-met", met);
    item.setAttribute("aria-label", `${item.textContent.trim()}${met ? ": met" : ""}`);
  });

  return Object.values(requirements).every(Boolean);
}

function initPasswordVisibility(input, button) {
  button.addEventListener("click", () => {
    const showing = input.type === "password";
    input.type = showing ? "text" : "password";
    button.setAttribute("aria-label", showing ? "Hide password" : "Show password");
    button.setAttribute("aria-pressed", String(showing));
    button.querySelector("svg").innerHTML = showing
      ? '<path d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a21.6 21.6 0 015.06-6.06M9.9 4.24A10.4 10.4 0 0112 4c7 0 11 8 11 8a21.6 21.6 0 01-3.22 4.44M14.12 14.12a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
      : '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/>';
  });
}

function initResetForm(token) {
  const form = document.getElementById("changePasswordForm");
  document.getElementById("passwordRequirements").hidden = false;
  const password = document.getElementById("newPassword");
  const confirmation = document.getElementById("confirmPassword");
  const passwordField = document.getElementById("newPasswordField");
  const confirmationField = document.getElementById("confirmPasswordField");
  const button = document.getElementById("changePasswordButton");
  const retryLink = document.getElementById("requestNewLink");
  const heading = document.getElementById("resetHeading");
  const description = document.getElementById("resetDescription");
  const idleText = button.textContent.trim();

  initPasswordVisibility(password, document.getElementById("toggleNewPassword"));
  initPasswordVisibility(
    confirmation,
    document.getElementById("toggleConfirmPassword"),
  );

  password.addEventListener("input", () => {
    passwordField.classList.remove("has-error");
    clearMessage();
    updatePasswordRequirements(password.value);
  });
  confirmation.addEventListener("input", () => {
    confirmationField.classList.remove("has-error");
    clearMessage();
  });

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    const requirementsMet = updatePasswordRequirements(password.value);
    const passwordsMatch =
      password.value.length > 0 && password.value === confirmation.value;
    passwordField.classList.toggle("has-error", !requirementsMet);
    confirmationField.classList.toggle("has-error", !passwordsMatch);

    if (!requirementsMet || !passwordsMatch) {
      (!requirementsMet ? password : confirmation).focus();
      return;
    }

    clearMessage();
    setBusy(button, true, idleText);

    try {
      const result = await resetPassword(token, password.value);
      showMessage(
        result.message ||
          "Your password has been updated successfully. You can now sign in using your new password.",
        "success",
      );
      heading.textContent = "Password updated";
      description.textContent =
        "Your password has been changed. You can now sign in using your new password.";
      form.hidden = true;
      retryLink.hidden = true;
      document.getElementById("backToLogin").textContent = "Sign in to your account";
    } catch (error) {
      console.error("Password reset failed:", error);
      showMessage(errorMessage(error, "Unable to update your password. Please try again."));
    } finally {
      setBusy(button, false, idleText);
    }
  });
}

async function initResetPage() {
  const form = document.getElementById("changePasswordForm");
  const retryLink = document.getElementById("requestNewLink");
  const heading = document.getElementById("resetHeading");
  const description = document.getElementById("resetDescription");
  const token = config.token;
  const currentUrl = new URL(window.location.href);
  currentUrl.searchParams.delete("token");
  window.history.replaceState(
    null,
    "",
    `${currentUrl.pathname}${currentUrl.search}${currentUrl.hash}`,
  );

  if (!/^[a-f\d]{64}$/i.test(token)) {
    showInvalidToken();
    return;
  }

  try {
    const result = await validateResetToken(token);
    if (!result.valid) {
      showInvalidToken();
      return;
    }

    form.hidden = false;
    initResetForm(token);
  } catch (error) {
    console.error("Reset token validation failed:", error);
    heading.textContent = "Unable to verify reset link";
    description.textContent =
      "We could not verify this link right now. Please try again or request a new reset link.";
    showMessage(
      errorMessage(error, "Unable to validate this reset link. Please try again."),
    );
    retryLink.hidden = false;
  }

  function showInvalidToken() {
    heading.textContent = "Reset link unavailable";
    description.textContent =
      "This password reset link is invalid, expired, or has already been used.";
    showMessage("Please request a new password reset link.");
    retryLink.hidden = false;
  }
}

if (config.mode === "request") {
  initRequestForm();
} else {
  initResetPage();
}
