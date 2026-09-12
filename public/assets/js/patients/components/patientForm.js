import { state } from "./state.js";
import { dom, selected } from "../utils/dom.js";
import { patientData } from "./patientData.js";
import { patientValidation } from "./patientValidation.js";
import { patientModal } from "./patientModal.js";
import { patientTable } from "./patientTable.js";

export const patientForm = {
  buildRecord() {
    const existing = state.editingId ? patientData.find(state.editingId) : null;

    return {
      id: dom.value("patientId"),
      basicInformation: {
        surname: dom.value("surName"),
        firstname: dom.value("firstName"),
        middlename: dom.value("middleName"),
        dob: dom.value("dateOfBirth"),
        gender: dom.value("gender"),
        civilStatus: dom.value("civilStatus"),
        religion: dom.value("religion"),
        nationality: dom.value("nationality"),
        department: dom.value("department"),
        position: dom.value("position"),
        contact: dom.value("contactNumber"),
        address: dom.value("address"),
        emergencyName: dom.value("emergencyName"),
        emergencyNumber: dom.value("emergencyNumber"),
        emergencyAddress: dom.value("emergencyAddress"),
        lastVisit: existing?.lastVisit || "Not yet visited",
        status: existing?.status || "Active",
      },
      physicalExamination: {
        id: dom.value("examinationId") || null,
        bloodPressure: dom.value("bloodPressure"),
        temperature: dom.value("temp"),
        pulseRate: dom.value("pulse"),
        respRate: dom.value("respRate"),
        height: dom.value("height"),
        weight: dom.value("weight"),
        idealWeight: dom.value("idealWeight"),
        headNeck: dom.value("headNeck"),
        respiratory: dom.value("respiratory"),
        cardioVascular: dom.value("cardioVascular"),
        gastroIntestinal: dom.value("gastroIntestinal"),
        genitoUrinary: dom.value("genitoUrinary"),
        extremities: dom.value("extremities"),
        neurologic: dom.value("neurologic"),
        suggestion: dom.value("suggestion"),
        laboratory: dom.value("laboratory"),
      },
      histories: {
        conditions: selected.array('input[name="history"]:checked'),
        previousHospitalization: dom.value("previousHospitalization"),
        previousOperation: dom.value("previousOperation"),
        previousTrauma: dom.value("previousTrauma"),
        socialHistory: {
          habits: selected.array('input[name="socialHistory"]:checked'),
          sportsDefinition: dom.value("sportsDefinition"),
        },
      },
    };
  },

  async submit(event) {
    event.preventDefault();

    if (state.viewOnly) return;
    if (!patientValidation.formIsValid()) return;

    const record = this.buildRecord();
    const isEditing = Boolean(state.editingId);

    StatusModal.confirm(
      isEditing ? "Confirm Patient Update" : "Confirm New Patient",
      isEditing
        ? "Are you sure you want to update this patient record?"
        : "Are you sure you want to add this new patient record?",
      async () => {
        try {
          await patientData.save(record);

          patientModal.close();
          patientTable.render();

          StatusModal.show(
            isEditing ? "Patient Updated" : "Patient Saved",
            `${patientData.fullName(record)}'s patient record was ${
              isEditing ? "updated" : "saved"
            } successfully.`,
            "success",
          );
        } catch (error) {
          console.error(error);

          StatusModal.show(
            "Error",
            "Unable to save the patient record. Please try again.",
            "error",
          );
        }
      },
    );
  },
};
