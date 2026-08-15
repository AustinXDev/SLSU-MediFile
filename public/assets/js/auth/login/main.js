import { initPassword } from "./password.js";

window.addEventListener("DOMContentLoaded", () => {
  initPassword();
  // Simple non-intrusive validation demo
  const form = document.getElementById("loginForm");
  const userField = document.getElementById("userField");
  const passField = document.getElementById("passField");
  const passInput = document.getElementById("password");

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    let valid = true;

    if (!document.getElementById("username").value.trim()) {
      userField.classList.add("has-error");
      valid = false;
    } else {
      userField.classList.remove("has-error");
    }

    if (!passInput.value.trim()) {
      passField.classList.add("has-error");
      valid = false;
    } else {
      passField.classList.remove("has-error");
    }

    if (valid) {
      // Placeholder: hook up real authentication here
      console.log("Form valid — ready to authenticate.");
    }
  });

  document
    .getElementById("username")
    .addEventListener("input", () => userField.classList.remove("has-error"));
  passInput.addEventListener("input", () =>
    passField.classList.remove("has-error"),
  );
});
