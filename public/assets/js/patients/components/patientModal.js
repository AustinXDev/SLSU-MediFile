import { serviceState, state } from "./state.js";
import { dom, selected } from "../utils/dom.js";
import { dateUtils } from "../utils/dateUtils.js";
import { patientData } from "./patientData.js";
import { patientValidation } from "./patientValidation.js";
import { calculateIdealWeight } from "../utils/calculateIdealWeight.js";
import { patientForm } from "./patientForm.js";

export const patientModal = {
  open(patient = null, viewOnly = false) {
    state.editingId = patient?.id || null;
    state.viewOnly = viewOnly;

    patientForm.initIdealWeight();

    dom.get("patientForm").reset();
    patientValidation.clear();

    const record = patient || { id: patientData.nextId() };
    const history = record?.past_history ? JSON.parse(record.past_history) : [];
    const socialHistory = record?.social_history
      ? JSON.parse(record.social_history)
      : [];
    const teethData = record?.teeth_data ? JSON.parse(record.teeth_data) : [];
    const treatmentPlan = record?.treatmentPlan ? record.treatmentPlan : [];

    serviceState.services = Array.isArray(record.dentalServices)
      ? record.dentalServices
      : [];

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
      idealWeight: calculateIdealWeight(record.height_cm, record.gender),
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

      inputs?.forEach((input) => {
        input.checked = historyValues.includes(input.value);
      });
    });

    this.renderDental(teethData);
    this.renderTreatmentPlan(treatmentPlan);
    serviceState.render();

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

        textarea.value = treatmentPlan?.[fieldKey] ?? "";
      });
  },
};

export const documentModal = {
  open(patient = null) {
    const history = patient?.past_history
      ? JSON.parse(patient.past_history)
      : [];
    const socialHistory = patient?.social_history
      ? JSON.parse(patient.social_history)
      : [];
    const teethData = patient?.teeth_data ? JSON.parse(patient.teeth_data) : [];
    const treatmentPlan = patient?.treatmentPlan ? patient.treatmentPlan : [];
    const dentalServices = patient?.dentalServices
      ? patient.dentalServices
      : [];

    console.log(patient);

    console.log(history);
    const elements = {
      documentLastName: patient.surname,
      documentFirstName: patient.firstname,
      documentMiddleName: patient.middlename,
      documentBirthday: patient.dob,
      documentAge: dateUtils.ageFromDob(patient.dob),
      documentSex: patient.gender,
      documentCivilStatus: patient.civilStatus,
      documentReligion: patient.religion,
      documentNationality: patient.nationality,
      documentDept: patient.department,
      documentCourse: patient.position,
      documentAddress: patient.address,
      documentTelNo: patient.contact,
      documentGuardian: patient.emergencyName,
      documentGuardianAdrress: patient.emergencyAddress,
      documentGuardianTelNo: patient.emergencyNumber,
      documentBloodPressure: patient.blood_pressure,
      documentTemp: patient.temperature,
      documentPulseRate: patient.pulse_rate,
      documentRespRate: patient.respiratory_rate,
      documentHeight: patient.height_cm,
      documentWeight: patient.weight_kg,
      documentIdealWeight: calculateIdealWeight(
        patient.height_cm,
        patient.gender,
      ),
      documentHeadNeck: patient.head_neck,
      documentResp: patient.respiratory,
      documentCardioVascular: patient.cardiovascular,
      documentGastroInternal: patient.gastrointestinal,
      documentGastroUrinary: patient.genitourinary,
      documentExtremities: patient.extremities,
      documentNeurologic: patient.neurologic,
      diagnosisText: patient.suggestions_treatment,
      laboratoryText: patient.laboratory_results,
      documentPrevHospitalization: patient.previous_hospitalization,
      documentPrevOperation: patient.previous_operation,
      documentPrevTrauma: patient.previous_trauma,
      documentSportDefinition: patient.sports_specification,
    };

    Object.entries(elements).forEach(([id, value]) => dom.setText(id, value));

    document.querySelectorAll(".document-history").forEach((element) => {
      element.textContent = history.includes(element.id) ? "/" : "";
    });

    document.querySelectorAll(".document-social-history").forEach((element) => {
      element.textContent = socialHistory.includes(element.id) ? "/" : "";
    });

    this.renderDental(teethData);
    this.renderTreatmentPlan(treatmentPlan);
    this.renderDentalService(dentalServices);

    dom.get("openDocumentPreview").classList.add("is-open");
  },

  close() {
    dom.get("openDocumentPreview").classList.remove("is-open");
  },

  renderDental(teethData) {
    document.querySelectorAll(".teeth-block-content").forEach((field) => {
      const toothId = field.id;

      if (!toothId) return;

      field.textContent = teethData?.[toothId] ?? "";
    });
  },

  renderTreatmentPlan(treatmentPlan) {
    document.querySelectorAll(".document-treatment-plan").forEach((element) => {
      const treatmentId = element.id;

      if (!treatmentId) return;

      element.textContent = treatmentPlan?.[treatmentId] ?? "";
    });
  },

  renderDentalService(dentalServices) {
    const rows = document.querySelectorAll(
      ".document-services-body tr[data-service-row]",
    );

    console.log(dentalServices);

    rows.forEach((row, index) => {
      const service = dentalServices?.[index];

      const date = row.querySelector(".service-date");
      const rendered = row.querySelector(".service-rendered");
      const patientSignature = row.querySelector(".service-patient-signature");
      const dentistSignature = row.querySelector(".service-dentist-signature");

      date.textContent = "";
      rendered.textContent = "";
      patientSignature.textContent = "";
      dentistSignature.textContent = "";

      if (!service) return;

      const signatureUrl = service.signaturePath
        ? `${window.API_URL}signature/dental-service-signature.php?path=${encodeURIComponent(
            service.signaturePath,
          )}`
        : null;

      date.textContent = service.serviceDate ?? "";
      rendered.textContent = service.serviceRendered;

      if (service.signaturePath) {
        const img = document.createElement("img");

        img.src = `${window.API_URL}signature/dental-service-signature.php?path=${encodeURIComponent(
          service.signaturePath,
        )}`;

        img.alt = "Patient signature";
        img.className = "service-signature-image";
        img.height = 20;
        img.width = 50;

        patientSignature.appendChild(img);
      }
    });
  },
};
