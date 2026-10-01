import { serviceModal } from "./serviceModal.js";
import { serviceForm } from "./serviceForm.js";
import { signaturePad } from "./signaturePad.js";
import { serviceState, state } from "./state.js";
import { Loader } from "../../components/loader.js";
import api from "../../../../api/api.js";
import { patientData } from "./patientData.js";

export const serviceEvents = {
  init() {
    this.bindOpen();
    this.bindClose();
    this.bindSignature();
    this.bindSave();
    this.bindRemove();
    this.bindDelete();
    signaturePad.init();
  },

  bindOpen() {
    document.addEventListener("click", (event) => {
      const button = event.target.closest("[data-add-service]");

      if (!button) return;

      serviceModal.open();
    });
  },

  bindClose() {
    document.addEventListener("click", (event) => {
      if (event.target.closest("[data-close-service-modal]")) {
        serviceModal.close();
      }
    });
  },

  bindSignature() {
    const clearButton = document.getElementById("clearPatientSignature");

    clearButton?.addEventListener("click", () => {
      signaturePad.clear();
    });
  },

  bindSave() {
    const saveButton = document.getElementById("saveServiceBtn");

    saveButton?.addEventListener("click", async () => {
      const response = await this.save();

      if (!response.success) {
        StatusModal.show(
          "Save Failed",
          "Unable to save the dental service. Please try again.",
          "error",
        );

        return;
      }

      StatusModal.show(
        "Service Saved",
        "The dental service has been successfully recorded.",
        "success",
      );

      serviceState.render();

      serviceModal.close();
    });
  },

  async save() {
    const service = serviceForm.buildRecord();
    let response = {
      success: true,
      errors: "",
    };

    const errors = serviceForm.validate(service);

    if (Object.keys(errors).length > 0) {
      response = { success: false, errors: errors };
      return response;
    }

    serviceState.add(service);

    return response;
  },

  bindRemove() {
    const tbody = document.getElementById("serviceHistoryBody");

    if (!tbody) return;

    tbody.addEventListener("click", (event) => {
      const button = event.target.closest("[data-remove-service]");

      console.log("remove clicked");

      if (!button) return;

      const index = Number(button.dataset.removeService);

      serviceState.remove(index);
      serviceState.render();
    });
  },

  bindDelete() {
    document.addEventListener("click", async (event) => {
      const button = event.target.closest(".saved-service-delete");

      if (!button) return;

      const serviceId = Number(button.dataset.deleteService);

      if (!serviceId) return;

      StatusModal.confirm(
        "Delete dental service?",
        "This will remove dental service from the list",
        async () => {
          await this.deleteSavedService(serviceId);
        },
      );
    });
  },

  async deleteSavedService(serviceId) {
    const load = new Loader({ text: "Deleting service..." });

    load.show();
    try {
      await api.post("patients/delete-dental-service.php", {
        data: {
          serviceId,
        },
      });

      serviceState.removeSaved(serviceId);

      await patientData.load();

      const patient = patientData.find(state.editingId);

      // Refresh service state
      serviceState.services = patient?.dentalServices || [];

      StatusModal.show(
        "Service Deleted",
        "The dental service was deleted successfully.",
        "success",
      );
    } catch (error) {
      console.error(error);

      StatusModal.show(
        "Delete Failed",
        "Unable to delete the dental service.",
        "error",
      );
    } finally {
      load.hide();
    }
  },
};
