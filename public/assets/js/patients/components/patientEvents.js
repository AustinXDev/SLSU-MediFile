import { state } from "./state.js";
import { dom } from "../utils/dom.js";
import { dateUtils } from "../utils/dateUtils.js";
import { patientData } from "./patientData.js";
import { patientSearch } from "./patientSearch.js";
import { patientTable } from "./patientTable.js";
import { patientModal } from "./patientModal.js";
import { patientForm } from "./patientForm.js";

export const patientEvents = {
  bind() {
    setTimeout(() => {
      if (dom.get("tableLoading")) dom.get("tableLoading").hidden = true;
      patientTable.render();
    }, 350);

    ["patientSearch", "genderFilter", "ageFilter", "statusFilter"].forEach(
      (id) => {
        dom.get(id)?.addEventListener("input", () => {
          state.page = 1;
          patientTable.render();
        });
      },
    );

    dom.get("headerSearch")?.addEventListener("input", () => {
      dom.setValue("patientSearch", dom.get("headerSearch").value);
      state.page = 1;
      patientTable.render();
    });

    dom.get("clearFilters")?.addEventListener("click", () => {
      patientSearch.clear();
      patientTable.render();
    });

    dom.get("emptyClear")?.addEventListener("click", () => {
      patientSearch.clear();
      patientTable.render();
    });

    dom
      .get("addPatientButton")
      ?.addEventListener("click", () => patientModal.open());

    dom
      .get("closePatientModal")
      ?.addEventListener("click", () => patientModal.close());

    dom
      .get("cancelPatient")
      ?.addEventListener("click", () => patientModal.close());

    dom
      .get("patientForm")
      ?.addEventListener("submit", (event) => patientForm.submit(event));

    dom
      .get("dateOfBirth")
      ?.addEventListener("input", () =>
        dom.setValue("age", dateUtils.ageFromDob(dom.value("dateOfBirth"))),
      );

    document.addEventListener("click", (e) => {
      if (e.target.closest("#patientsBody button[data-action]")) {
        this.handlePatientAction(e);
      }

      console.log("even clicked");

      // Check pagination clicks
      if (e.target.closest("#pagination button[data-page]")) {
        this.handlePageChange(e);
      }
    });

    dom.get("patientModal")?.addEventListener("click", (event) => {
      if (event.target === dom.get("patientModal")) patientModal.close();
    });
  },

  handlePatientAction(event) {
    const button = event.target.closest("button[data-action]");
    if (!button) return;

    const action = button.dataset.action;
    const id = button.dataset.id;

    console.log("Clicked action:", action, "for ID:", id); // Now correctly defined

    const patient = patientData.find(id);
    if (!patient) {
      console.warn(`No patient found in state matching ID: ${id}`);
      return;
    }

    if (action === "delete") {
      if (typeof StatusModal !== "undefined") {
        StatusModal.confirm(
          "Delete patient record?",
          `This will remove ${patientData.fullName(patient)}'s record from the patient list.`,
          () => {
            patientData.remove(patient.id);
            patientTable.render();
          },
        );
      } else if (confirm(`Delete ${patientData.fullName(patient)}?`)) {
        patientData.remove(patient.id);
        patientTable.render();
      }
      return;
    }

    patientModal.open(patient, action === "view");
  },

  handlePageChange(event) {
    const button = event.target.closest("button[data-page]");
    if (!button || button.disabled) return;

    state.page = Number(button.dataset.page);
    patientTable.render();
  },
};
