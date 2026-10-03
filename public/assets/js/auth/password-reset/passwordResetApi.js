import api from "../../../../api/api.js";

export async function requestPasswordReset(email) {
  const response = await api.post("auth/request-password-reset.php", { email });
  return response.data;
}

export async function validateResetToken(token) {
  const response = await api.get("auth/validate-reset-token.php", {
    params: { token },
  });
  return response.data;
}

export async function resetPassword(token, password) {
  const response = await api.post("auth/reset-password.php", {
    token,
    password,
  });
  return response.data;
}
