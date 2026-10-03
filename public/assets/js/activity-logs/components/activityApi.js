import api from "../../../../api/api.js";

async function request(params = {}) {
  try {
    const response = await api.get("activity-logs/index.php", { params });
    const payload = response.data;

    if (payload?.status === "error") {
      throw new Error(payload.message || "Unable to load activity logs.");
    }

    return payload?.data;
  } catch (error) {
    const message = error.response?.data?.message;
    if (message) {
      throw new Error(message);
    }

    throw error;
  }
}

export const activityApi = {
  getActivity(params) {
    return request(Object.fromEntries(params.entries()));
  },

  getDetails(id) {
    return request({ id });
  },
};
