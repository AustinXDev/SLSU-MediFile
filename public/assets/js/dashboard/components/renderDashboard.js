import { getData } from "./dashboardData.js";
import { dom } from "../utils/dom.js";
import { progress } from "../utils/progress.js";
import { chart } from "./chart.js";
import { getInitials } from "../utils/getInitials.js";
import { formatDateTime } from "../utils/formatDateTime.js";
import { formatActivity } from "../utils/formatActivity.js";
import { getAvatarClass } from "../utils/getAvatarClass.js";
import { escapeHtml } from "../utils/html.js";

export const dashboard = {
  async init() {
    try {
      const data = await getData();

      console.log("Dashboard data:", data);

      this.render(data);
    } catch (error) {
      console.error("Failed to initialize dashboard:", error);
    }
  },

  render(data) {
    const kpi = data?.kpis;
    const activity = data?.patientActivity;
    const recordsOverview = data?.recordsOverview;
    const recentActivity = data?.recentActivity;
    const recentPatients = data?.recentPatients;

    if (kpi) {
      this.renderKpis(kpi);
    }

    if (activity) {
      this.renderPatientActivity(activity);
    }

    if (recordsOverview) {
      this.renderMedicalRecordsOverview(recordsOverview);
    }

    if (recentActivity) {
      this.renderRecentActivity(recentActivity);
    }

    if (recentPatients) {
      this.renderRecentPatients(recentPatients);
    }

    console.log(kpi);
    console.log(activity);
  },

  renderKpis(kpi) {
    //kpi data
    const totalPatients = kpi.totalPatients;
    const totalUsers = kpi.totalUsers;
    const activePatients = kpi.activePatients;
    const todayPatientRecords = kpi.todayPatientRecords;
    const totalDentalServices = kpi.totalDentalServices;

    //Set KPI Content
    dom.setNumber("totalPatient", totalPatients?.count);
    dom.setChange(
      "totalPatientChange",
      totalPatients?.change,
      totalPatients?.direction,
    );

    dom.setNumber("totalActiveRecords", activePatients?.count);
    dom.setChange(
      "totalActiveChange",
      activePatients?.change,
      activePatients?.direction,
    );

    dom.setNumber("totalServices", todayPatientRecords?.count);
    dom.setChange(
      "totalServiceChange",
      todayPatientRecords?.change,
      todayPatientRecords?.direction,
    );

    dom.setNumber("totalDentalServices", totalDentalServices?.count);
    dom.setChange(
      "totalDentalChange",
      totalDentalServices?.change,
      totalDentalServices?.direction,
    );

    dom.setNumber("totalStaffs", totalUsers?.count);
    dom.setChange(
      "totalStaffChange",
      totalUsers?.change,
      totalUsers?.direction,
    );
  },

  renderPatientActivity(activity) {
    chart.initActivityFilter(activity);

    chart.renderActivityPeriod(activity, "weekly");
  },

  renderMedicalRecordsOverview(records) {
    const total = Number(records?.totalRecords) || 0;

    const newThisMonth = Number(records?.newThisMonth) || 0;
    const updatedThisMonth = Number(records?.updatedThisMonth) || 0;
    const medicalExaminations = Number(records?.medicalExaminations) || 0;
    const dentalRecords = Number(records?.dentalRecords) || 0;

    // Numbers
    dom.setNumber("newRecords", newThisMonth);
    dom.setNumber("updatedRecords", updatedThisMonth);
    dom.setNumber("medicalRecords", medicalExaminations);
    dom.setNumber("dentalRecords", dentalRecords);
    dom.setNumber("totalMedicalRecords", total);

    // Percentages
    const newPercent = progress.calculatePercentage(newThisMonth, total);

    const updatedPercent = progress.calculatePercentage(
      updatedThisMonth,
      total,
    );

    const medicalPercent = progress.calculatePercentage(
      medicalExaminations,
      total,
    );

    const dentalPercent = progress.calculatePercentage(dentalRecords, total);

    // Progress bars
    progress.setProgress("newRecordsProgress", newPercent);

    progress.setProgress("updatedRecordsProgress", updatedPercent);

    progress.setProgress("medicalRecordsProgress", medicalPercent);

    progress.setProgress("dentalRecordsProgress", dentalPercent);

    // Percentage labels
    dom.setText("newRecordsPercent", `${newPercent}%`);

    dom.setText("updatedRecordsPercent", `${updatedPercent}%`);

    dom.setText("medicalRecordsPercent", `${medicalPercent}%`);

    dom.setText("incompleteRecordsPercent", `${dentalPercent}%`);
  },

  renderRecentActivity(activities) {
    const tbody = dom.get("recentActivityBody");

    if (!tbody) return;

    tbody.innerHTML = "";

    if (!Array.isArray(activities) || activities.length === 0) {
      tbody.innerHTML = `
      <tr>
        <td colspan="4" class="empty-state">
          No recent activity.
        </td>
      </tr>
    `;

      return;
    }

    activities.forEach((activity) => {
      const username = activity.username || "System";

      const initials = getInitials(username);

      const action = formatActivity(activity);

      const dateTime = formatDateTime(activity.created_at);

      tbody.insertAdjacentHTML(
        "beforeend",
        `
        <tr>
          <td>
            <div class="user-cell">
              <div class="user-avatar ${getAvatarClass(username)}">
                ${escapeHtml(initials)}
              </div>

              <span>
                ${escapeHtml(username)}
              </span>
            </div>
          </td>

          <td>
            ${escapeHtml(action)}
          </td>

          <td>
            ${escapeHtml(dateTime)}
          </td>

          <td>
            <span class="status completed">
              Completed
            </span>
          </td>
        </tr>
      `,
      );
    });
  },

  renderRecentPatients(patients) {
    const tbody = document.getElementById("recentPatientsBody");

    if (!tbody) return;

    tbody.innerHTML = "";

    if (!Array.isArray(patients) || patients.length === 0) {
      tbody.innerHTML = `
      <tr>
        <td colspan="3" class="empty-state" style="text-align: center;">
          No patient records found.
        </td>
      </tr>
    `;

      return;
    }

    patients.forEach((patient) => {
      const fullName = [patient.firstname, patient.middlename, patient.surname]
        .filter(Boolean)
        .join(" ")
        .replace(/\s+/g, " ")
        .trim();

      const department = patient.college_dept || "—";

      const dateAdded = formatDateTime(patient.created_at);

      tbody.insertAdjacentHTML(
        "beforeend",
        `
        <tr>

          <td>
            <div class="user-cell">

              <div class="user-avatar ${getAvatarClass(fullName)}">
                ${escapeHtml(getInitials(fullName))}
              </div>

              <span>
                ${escapeHtml(fullName || "Unknown Patient")}
              </span>

            </div>
          </td>

          <td>
            ${escapeHtml(department)}
          </td>

          <td>
            ${escapeHtml(dateAdded)}
          </td>

        </tr>
      `,
      );
    });
  },
};
