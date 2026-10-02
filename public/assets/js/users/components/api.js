import api from "../../../../api/api.js";

const API_BASE = window.APP_API || "http://localhost/SLSU-MediFile/api/";

export function getUrl(path) {
  return `${API_BASE.replace(/\/$/, "")}/${path.replace(/^\//, "")}`;
}

export async function fetchJson(url, options = {}) {
  const { body, ...requestOptions } = options;

  try {
    const response = await api.request({
      url,
      ...requestOptions,
      data: body,
      headers: {
        ...(body ? { "Content-Type": "application/json" } : {}),
        ...(requestOptions.headers || {}),
      },
    });

    const result = response.data;
    if (result?.status === "error") {
      throw new Error(result.message || "Request failed.");
    }

    return result;
  } catch (error) {
    const message = error.response?.data?.message;
    if (message) {
      throw new Error(message);
    }

    throw error;
  }
}
