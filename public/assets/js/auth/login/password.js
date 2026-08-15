export function initPassword() {
  const passInput = document.getElementById("password");
  const toggleBtn = document.getElementById("togglePass");
  const eyeIcon = document.getElementById("eyeIcon");

  toggleBtn.addEventListener("click", () => {
    const isPassword = passInput.type === "password";
    passInput.type = isPassword ? "text" : "password";
    toggleBtn.setAttribute(
      "aria-label",
      isPassword ? "Hide password" : "Show password",
    );
    eyeIcon.innerHTML = isPassword
      ? '<path d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a21.6 21.6 0 015.06-6.06M9.9 4.24A10.4 10.4 0 0112 4c7 0 11 8 11 8a21.6 21.6 0 01-3.22 4.44M14.12 14.12a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
      : '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/>';
  });
}
