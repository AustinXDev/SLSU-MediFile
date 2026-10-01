import api from "../../../../api/api.js";

export async function getData() {
  try {
    const response = await api.get("dashboard/get-dashboard.php");

    return response.data?.data ?? {};
  } catch (error) {
    console.error("Failed to load dashboard data:", error);
    throw error;
  }
}
