import api from "../../../../api/api.js";
import { getMockDashboardData } from "./mockCsmDashboardData.js";

export const USE_MOCK_DATA = false; // Set to true to use mock data for testing

export async function getData(params) {
  if (USE_MOCK_DATA) {
    return getMockDashboardData(params);
  }

  try {
    const response = await api.get("dashboard/get-csm-dashboard.php", {
      params,
    });

    if (response.data?.status !== "success") {
      throw new Error(response.data?.message || "Unable to load results.");
    }

    return response.data?.data ?? {};
  } catch (error) {
    console.error("Failed to load CSM dashboard data:", error);
    throw error;
  }
}
