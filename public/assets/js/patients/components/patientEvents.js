import { state } from "./state.js";
import { dom, selected } from "../utils/dom.js";
import { dateUtils } from "../utils/dateUtils.js";
import { patientData } from "./patientData.js";
import { patientSearch } from "./patientSearch.js";
import { patientTable } from "./patientTable.js";
import { patientModal, documentModal } from "./patientModal.js";
import { patientForm } from "./patientForm.js";
import { dentalRecordValidation } from "./patientValidation.js";
import { Loader } from "../../components/loader.js";
import { serviceState } from "./state.js";
import { downloadDocument } from "../utils/downloadDocument.js";

let patientSearchTimer;

async function reloadPatients() {
  await patientData.loadPatients();
  patientTable.render();
}

function schedulePatientSearch(value) {
  window.clearTimeout(patientSearchTimer);
  patientSearchTimer = window.setTimeout(() => {
    state.search = String(value ?? "").trim();
    state.page = 1;
    reloadPatients().catch((error) =>
      console.error("Unable to search patient records:", error),
    );
  }, 300);
}

export const patientEvents = {
  bind() {
    setTimeout(() => {
      if (dom.get("tableLoading")) dom.get("tableLoading").hidden = true;
    }, 350);

    dom.get("patientSearch")?.addEventListener("input", (event) => {
      schedulePatientSearch(event.target.value);
    });

    dom.get("headerSearch")?.addEventListener("input", (event) => {
      dom.setValue("patientSearch", event.target.value);
      schedulePatientSearch(event.target.value);
    });

    ["genderFilter", "ageFilter", "statusFilter"].forEach((id) => {
      dom.get(id)?.addEventListener("change", (event) => {
        const stateKey = {
          genderFilter: "gender",
          ageFilter: "ageGroup",
          statusFilter: "status",
        }[id];

        state[stateKey] = event.target.value;
        state.page = 1;
        reloadPatients().catch((error) =>
          console.error("Unable to filter patient records:", error),
        );
      });
    });

    dom.get("clearFilters")?.addEventListener("click", () => {
      window.clearTimeout(patientSearchTimer);
      patientSearch.clear();
      reloadPatients().catch((error) =>
        console.error("Unable to load patient records:", error),
      );
    });

    dom.get("emptyClear")?.addEventListener("click", () => {
      window.clearTimeout(patientSearchTimer);
      patientSearch.clear();
      reloadPatients().catch((error) =>
        console.error("Unable to load patient records:", error),
      );
    });

    dom
      .get("addPatientButton")
      ?.addEventListener("click", () => patientModal.open());

    dom.get("closePatientModal")?.addEventListener("click", () => {
      patientForm.showPage(1);
      serviceState.clear();
      patientModal.close();

      console.log(serviceState.getAll());
    });

    dom.get("cancelPatient")?.addEventListener("click", () => {
      serviceState.clear();
      patientModal.close();
    });

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

    dom.get("printDocument")?.addEventListener("click", () => {
      window.print();
    });

    dom.get("downloadDocument")?.addEventListener("click", () => {
      downloadDocument();
    });

    selected.get(".dental-field")?.forEach((field) => {
      field.addEventListener("blur", (e) => {
        const value = e.target.value;
        const id = field.id;

        dentalRecordValidation.validate(id, value);
      });
    });

    dom.get("closeDocument")?.addEventListener("click", () => {
      documentModal.close();
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
              const response = await patientData.remove(patient.id);

              if (response.data?.status === "error") {
                StatusModal.show("Failed", "Failed to delete patient record.");
                return;
              }

              StatusModal.show(
                "Success",
                `${patientData.fullName(patient)}'s record was successfully deleted.`,
              );

              await reloadPatients();
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

    if (action === "view") {
      documentModal.open(patient);
      return;
    }

    patientModal.open(patient, false);
  },

  handlePageChange(event) {
    const button = event.target.closest("button[data-page]");
    if (!button || button.disabled) return;

    const page = Number(button.dataset.page);
    if (
      !Number.isInteger(page) ||
      page < 1 ||
      page > state.totalPages ||
      page === state.page
    ) {
      return;
    }

    state.page = page;
    reloadPatients().catch((error) =>
      console.error("Unable to load patient page:", error),
    );
  },
};
