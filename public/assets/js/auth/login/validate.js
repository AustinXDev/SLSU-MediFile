export function validateInput({ username, password }) {
  const usernameInput = username.trim();
  const passwordInput = password.trim();

  if (!usernameInput && !passwordInput) {
    return {
      input: "both",
      return: "false",
    };
  }

  if (!usernameInput) {
    return {
      input: "username",
      valid: false,
    };
  }

  if (!passwordInput) {
    return {
      input: "password",
      valid: false,
    };
  }

  return true;
}
