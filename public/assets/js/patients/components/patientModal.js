import { state } from "./state.js";
import { dom, selected } from "../utils/dom.js";
import { dateUtils } from "../utils/dateUtils.js";
import { patientData } from "./patientData.js";
import { patientValidation } from "./patientValidation.js";

export const patientModal = {
  open(patient = null, viewOnly = false) {
    state.editingId = patient?.id || null;
    state.viewOnly = viewOnly;

    console.log("editing state ", state.editingId);

    dom.get("patientForm").reset();
    patientValidation.clear();

    const record = patient || { id: patientData.nextId() };
    const history = record?.past_history ? JSON.parse(record.past_history) : [];
    const socialHistory = record?.social_history
      ? JSON.parse(record.social_history)
      : [];
    const teethData = record?.teeth_data ? JSON.parse(record.teeth_data) : [];
    const treatmentPlan = record?.treatmentPlan ? record.treatmentPlan : [];

    console.log(history);
    console.log(teethData);

    const fields = {
      patientId: record.id,
      examinationId: record.examinationId,
      dentalId: record.dental_id,
      patientIdDisplay: record.id,
      surName: record.surname,
      firstName: record.firstname,
      middleName: record.middlename,
      dateOfBirth: record.dob,
      age: dateUtils.ageFromDob(record.dob),
      gender: record.gender,
      civilStatus: record.civilStatus,
      religion: record.religion,
      nationality: record.nationality,
      department: record.department,
      position: record.position,
      contactNumber: record.contact,
      address: record.address,
      emergencyName: record.emergencyName,
      emergencyNumber: record.emergencyNumber,
      emergencyAddress: record.emergencyAddress,
      bloodPressure: record.blood_pressure,
      temp: record.temperature,
      pulse: record.pulse_rate,
      respRate: record.respiratory_rate,
      height: record.height_cm,
      weight: record.weight_kg,
      idealWeight: record.ideal_body_weight_kg,
      headNeck: record.head_neck,
      respiratory: record.respiratory,
      cardioVascular: record.cardiovascular,
      gastroIntestinal: record.gastrointestinal,
      genitoUrinary: record.genitourinary,
      extremities: record.extremities,
      neurologic: record.neurologic,
      suggestion: record.suggestions_treatment,
      laboratory: record.laboratory_results,
      previousHospitalization: record.previous_hospitalization,
      previousOperation: record.previous_operation,
      previousTrauma: record.previous_trauma,
      sportsDefinition: record.sports_specification,
    };

    const name = {
      history: history,
      socialHistory: socialHistory,
    };

    Object.entries(fields).forEach(([id, value]) => dom.setValue(id, value));

    Object.entries(name).forEach(([fieldName, historyValues]) => {
      const inputs = selected.get(`input[name=${fieldName}]`);
      console.log("Inputs: ", inputs);
      console.log("values: ", historyValues);

      inputs?.forEach((input) => {
        input.checked = historyValues.includes(input.value);
      });
    });

    this.renderDental(teethData);
    this.renderTreatmentPlan(treatmentPlan);

    dom.get("patientModalTitle").textContent = viewOnly
      ? "Patient Record"
      : patient
        ? "Edit Patient Record"
        : "Add Patient Record";

    dom.get("patientModalDescription").textContent = viewOnly
      ? "Review the saved patient information."
      : "Enter the patient's information below.";

    dom.get("patientForm").classList.toggle("view-mode", viewOnly);
    dom.get("patientModal").hidden = false;

    document.body.style.overflow = "hidden";
  },

  close() {
    dom.get("patientModal").hidden = true;
    document.body.style.overflow = "";
  },

  renderDental(teethData) {
    selected.get(".dental-field").forEach((field) => {
      const toothId = field.id;

      if (!toothId) return;

      field.value = teethData?.[toothId] ?? "";
      field.classList.remove("has-error");
    });
  },

  renderTreatmentPlan(treatmentPlan) {
    selected
      .get('.treatment-field textarea[name^="treatment_plan["]')
      .forEach((textarea) => {
        const match = textarea.name.match(/^treatment_plan\[(.+)\]$/);

        if (!match) return;

        const fieldKey = match[1];

        console.log("Field key:", fieldKey);

        textarea.value = treatmentPlan?.[fieldKey] ?? "";
      });
  },
};
