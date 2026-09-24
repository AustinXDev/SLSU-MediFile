import { state } from "./state.js";
import { dom, selected } from "../utils/dom.js";
import { dateUtils } from "../utils/dateUtils.js";
import { patientData } from "./patientData.js";
import { patientSearch } from "./patientSearch.js";
import { patientTable } from "./patientTable.js";
import { patientModal } from "./patientModal.js";
import { patientForm } from "./patientForm.js";
import { dentalRecordValidation } from "./patientValidation.js";
import { Loader } from "../../components/loader.js";

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

    dom.get("closePatientModal")?.addEventListener("click", () => {
      patientForm.showPage(1);
      patientModal.close();
    });

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

    dom.get("next")?.addEventListener("click", () => patientForm.nextPage());

    dom
      .get("previous")
      ?.addEventListener("click", () => patientForm.previousPage());

    document.addEventListener("click", (e) => {
      if (e.target.closest("#patientsBody button[data-action]")) {
        this.handlePatientAction(e);
      }

      // Check pagination clicks
      if (e.target.closest("#pagination button[data-page]")) {
        this.handlePageChange(e);
      }
    });

    selected.get(".dental-field")?.forEach((field) => {
      field.addEventListener("blur", (e) => {
        const value = e.target.value;
        const id = field.id;

        dentalRecordValidation.validate(id, value);
      });
    });
  },

  handlePatientAction(event) {
    const button = event.target.closest("button[data-action]");
    if (!button) return;

    const action = button.dataset.action;
    const id = button.dataset.id;

    const patient = patientData.find(id);
    if (!patient) {
      console.warn(`No patient found in state matching ID: ${id}`);
      return;
    }

    const load = new Loader({
      text: "Deleting patient record...",
    });

    if (action === "delete") {
      if (typeof StatusModal !== "undefined") {
        StatusModal.confirm(
          "Delete patient record?",
          `This will remove ${patientData.fullName(patient)}'s record from the patient list.`,
          async () => {
            load.show();
            try {
              const response = patientData.remove(patient.id);

              if (response.status === "error") {
                StatusModal.show("Failed", "Failed to delete patient record.");
              }

              StatusModal.show(
                "Success",
                `${patientData.fullName(patient)}'s record was successfully deleted.`,
              );

              await patientData.load();

              patientTable.render();
            } catch (error) {
              load.hide();
              console.error(error);
              StatusModal.show("Failed", "Failed to delete patient record.");
            } finally {
              load.hide();
            }
          },
        );
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
