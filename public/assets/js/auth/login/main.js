import { initPassword } from "./password.js";
import { validateInput } from "./validate.js";

window.addEventListener("DOMContentLoaded", () => {
  initPassword();
  // Simple non-intrusive validation demo
  const form = document.getElementById("loginForm");
  const userField = document.getElementById("userField");
  const passField = document.getElementById("passField");
  const passInput = document.getElementById("password");

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    console.log(username, password);

    const validate = validateInput({
      username,
      password,
    });

    if (!validate.valid && validate.input === "both") {
      userField.classList.add("has-error");
      passField.classList.add("has-error");
      return;
    }

    if (!validate.valid && validate.input === "username") {
      userField.classList.add("has-error");
      return;
    }

    if (!validate.valid && validate.input === "password") {
      passField.classList.add("has-error");
      return;
    }

    console.log("valid");
  });

  document
    .getElementById("username")
    .addEventListener("input", () => userField.classList.remove("has-error"));
  passInput.addEventListener("input", () =>
    passField.classList.remove("has-error"),
  );
});
