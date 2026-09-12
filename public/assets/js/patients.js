import api from "../../api/api.js";

(() => {
  "use strict";

  const samplePatients = [
    {
      id: "P-2025-0001",
      firstname: "Maria",
      middlename: "Miguel",
      surname: "Santos",
      dob: "1988-04-12",
      gender: "Female",
      civilStatus: "Single",
      religion: "Catholic",
      nationality: "Filipino",
      department: "IT Department",
      position: "Bachelor of Science in Information Teachnology",
      address: "Brgy. Maligaya Candelaria Quezon",
      contact: "0917 555 0142",
      emergencyName: "Joselito Santos",
      emergencyAddress: "Brgy. Maligaya Candelaria Quezon",
      emergencyNumber: "0908 019 5555",
      lastVisit: "May 22, 2025",
      status: "Active",
    },
  ];

  const state = {
    patients: samplePatients,
    page: 1,
    pageSize: 5,
    editingId: null,
    viewOnly: false,
  };

  const dom = {
    get(id) {
      return document.getElementById(id);
    },
    value(id) {
      return this.get(id)?.value.trim() || "";
    },
    setValue(id, value) {
      const element = this.get(id);
      if (element) element.value = value || "";
    },
  };

  const dateUtils = {
    ageFromDob(dob) {
      if (!dob) return "";
      const birthDate = new Date(`${dob}T00:00:00`);
      if (Number.isNaN(birthDate.getTime())) return "";

      const today = new Date();
      let age = today.getFullYear() - birthDate.getFullYear();
      const beforeBirthday =
        today.getMonth() < birthDate.getMonth() ||
        (today.getMonth() === birthDate.getMonth() &&
          today.getDate() < birthDate.getDate());

      if (beforeBirthday) age -= 1;
      return age >= 0 ? age : "";
    },
  };

  const patientData = {
    fullName(patient) {
      return [patient.firstname, patient.middlename, patient.surname]
        .filter(Boolean)
        .join(" ")
        .replace(/\s+/g, " ")
        .trim();
    },
    nextId() {
      const highestId = state.patients.reduce(
        (highest, patient) =>
          Math.max(highest, Number(patient.id.split("-").pop())),
        0,
      );
      return `P-${new Date().getFullYear()}-${String(highestId + 1).padStart(4, "0")}`;
    },
    find(id) {
      return state.patients.find((patient) => patient.id === id);
    },
    async save(record) {
      if (state.editingId) {
        state.patients = state.patients.map((patient) =>
          patient.id === state.editingId ? record : patient,
        );
      } else {
        try {
          return await api.post("patients/create.php", { record });
        } catch (error) {
          console.error(error.message);
        }
      }
    },
    remove(id) {
      state.patients = state.patients.filter((patient) => patient.id !== id);
    },
  };

  const search = {
    getFilters() {
      return {
        query: dom.value("patientSearch").toLowerCase(),
        gender: dom.get("genderFilter").value,
        ageGroup: dom.get("ageFilter").value,
        status: dom.get("statusFilter").value,
      };
    },
    matchesAgeGroup(age, ageGroup) {
      return (
        !ageGroup ||
        (ageGroup === "child" && age < 18) ||
        (ageGroup === "adult" && age >= 18 && age < 60) ||
        (ageGroup === "senior" && age >= 60)
      );
    },
    filterPatients() {
      const filters = this.getFilters();
      return state.patients.filter((patient) => {
        const name = patientData.fullName(patient).toLowerCase();
        const age = dateUtils.ageFromDob(patient.dob);
        const matchesQuery =
          !filters.query ||
          [name, patient.id.toLowerCase(), patient.contact.toLowerCase()].some(
            (value) => value.includes(filters.query),
          );

        return (
          matchesQuery &&
          (!filters.gender || patient.gender === filters.gender) &&
          (!filters.status || patient.status === filters.status) &&
          this.matchesAgeGroup(age, filters.ageGroup)
        );
      });
    },
    hasActiveFilters() {
      const filters = this.getFilters();
      return Boolean(
        filters.query || filters.gender || filters.ageGroup || filters.status,
      );
    },
    clear() {
      ["patientSearch", "headerSearch"].forEach((id) => dom.setValue(id, ""));
      ["genderFilter", "ageFilter", "statusFilter"].forEach((id) =>
        dom.setValue(id, ""),
      );
      state.page = 1;
      table.render();
    },
  };

  const html = {
    escape(value) {
      return String(value ?? "").replace(/[&<>'"]/g, (character) => {
        const entities = {
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          "'": "&#39;",
          '"': "&quot;",
        };
        return entities[character];
      });
    },
  };

  const table = {
    initials(name) {
      return name
        .split(" ")
        .map((part) => part[0])
        .slice(0, 2)
        .join("")
        .toUpperCase();
    },
    row(patient) {
      const name = patientData.fullName(patient);
      const escapedName = html.escape(name);
      const statusClass =
        patient.status === "Active" ? "completed" : "inactive";

      return `<tr>
        <td><span class="patient-id">${html.escape(patient.id)}</span></td>
        <td><div class="patient-name">
          <span class="patient-avatar">${this.initials(name)}</span>
          <div><strong>${escapedName}</strong><span> Patient record</span></div>
        </div></td>
        <td style="font-size: 11px">${dateUtils.ageFromDob(patient.dob)}</td>
        <td style="font-size: 11px">${html.escape(patient.gender)}</td>
        <td style="font-size: 11px">${html.escape(patient.contact)}</td>
        <td style="font-size: 11px">${html.escape(patient.lastVisit || "N/A")}</td>
        <td><span style="font-size: 9px" class="status ${statusClass}">${html.escape(patient.status)}</span></td>
        <td><div class="action-buttons">
          <button class="action-button" title="View patient record" aria-label="View ${escapedName}" data-action="view" data-id="${html.escape(patient.id)}"><i class="fa-solid fa-eye"></i></button>
          <button class="action-button" title="Edit patient information" aria-label="Edit ${escapedName}" data-action="edit" data-id="${html.escape(patient.id)}"><i class="fa-solid fa-pen"></i></button>
          <button class="action-button delete" title="Delete patient record" aria-label="Delete ${escapedName}" data-action="delete" data-id="${html.escape(patient.id)}"><i class="fa-solid fa-trash"></i></button>
        </div></td>
      </tr>`;
    },
    pagination(totalPages) {
      const pageButtons = Array.from(
        { length: totalPages },
        (_, index) =>
          `<button type="button" class="${state.page === index + 1 ? "active" : ""}" aria-label="Page ${index + 1}" data-page="${index + 1}">${index + 1}</button>`,
      ).join("");

      return `<button type="button" aria-label="Previous page" data-page="${state.page - 1}" ${state.page === 1 ? "disabled" : ""}><i class="fa-solid fa-chevron-left"></i></button>${pageButtons}<button type="button" aria-label="Next page" data-page="${state.page + 1}" ${state.page === totalPages ? "disabled" : ""}><i class="fa-solid fa-chevron-right"></i></button>`;
    },
    render() {
      const results = search.filterPatients();
      const totalPages = Math.max(
        1,
        Math.ceil(results.length / state.pageSize),
      );
      state.page = Math.min(state.page, totalPages);
      const start = (state.page - 1) * state.pageSize;
      const pageRows = results.slice(start, start + state.pageSize);

      dom.get("patientsBody").innerHTML = pageRows
        .map((patient) => this.row(patient))
        .join("");
      dom.get("patientsTable").hidden = pageRows.length === 0;
      dom.get("emptyState").hidden = pageRows.length !== 0;
      dom.get("recordCount").textContent =
        `${results.length} patient${results.length === 1 ? "" : "s"}`;
      dom.get("resultSummary").textContent = search.hasActiveFilters()
        ? `${results.length} search result${results.length === 1 ? "" : "s"}`
        : "Showing patient records";
      dom.get("pagination").innerHTML = this.pagination(totalPages);
    },
  };

  const validation = {
    requiredFields: [
      ["firstName", "First name is required."],
      ["surName", "Surname is required."],
      ["middleName", "Middle name is required."],
      ["dateOfBirth", "Date of birth is required."],
      ["gender", "Please select a gender."],
      ["civilStatus", "Please select a civil status."],
      ["religion", "Religion is required."],
      ["nationality", "Nationality is required."],
      ["department", "College or department is required."],
      ["position", "Job position or course is required."],
      ["contactNumber", "Contact number is required."],
      ["emergencyName", "Emergency contact name is required."],
      ["emergencyNumber", "Emergency contact number is required."],
      ["emergencyAddress", "Emergency address is required."],
    ],
    clear() {
      document.querySelectorAll(".form-field").forEach((field) => {
        field.classList.remove("has-error");
        const error = field.querySelector(".field-error");
        if (error) error.textContent = "";
      });
    },
    setError(id, message) {
      const field = dom.get(id)?.closest(".form-field");
      if (!field) return;
      field.classList.add("has-error");
      const error = field.querySelector(".field-error");
      if (error) error.textContent = message;
    },
    formIsValid() {
      this.clear();
      let valid = true;

      this.requiredFields.forEach(([id, message]) => {
        if (!dom.value(id)) {
          this.setError(id, message);
          valid = false;
        }
      });

      if (
        dom.value("dateOfBirth") &&
        dateUtils.ageFromDob(dom.value("dateOfBirth")) === ""
      ) {
        this.setError("dateOfBirth", "Enter a valid date of birth.");
        valid = false;
      }
      return valid;
    },
  };

  const modal = {
    open(patient = null, viewOnly = false) {
      state.editingId = patient?.id || null;
      state.viewOnly = viewOnly;
      dom.get("patientForm").reset();
      validation.clear();

      const record = patient || { id: patientData.nextId() };
      const fields = {
        patientId: record.id,
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
      };
      Object.entries(fields).forEach(([id, value]) => dom.setValue(id, value));

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
  };

  const form = {
    buildRecord() {
      const existing = state.editingId
        ? patientData.find(state.editingId)
        : null;
      return {
        id: dom.value("patientId"),
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
      };
    },
    submit(event) {
      event.preventDefault();
      if (!validation.formIsValid()) return;

      const record = this.buildRecord();
      StatusModal.confirm(
        "Confirm New Patient",
        "Are you sure you want to add this new patient record?",
        () => {
          patientData.save(record);

          modal.close();

          table.render();

          StatusModal.show(
            "Patient Saved",
            `${patientData.fullName(record)}'s patient record was saved successfully.`,
            "success",
          );
        },
      );
    },
  };

  const events = {
    bind() {
      setTimeout(() => {
        dom.get("tableLoading").hidden = true;
        table.render();
      }, 350);

      ["patientSearch", "genderFilter", "ageFilter", "statusFilter"].forEach(
        (id) =>
          dom.get(id).addEventListener("input", () => {
            state.page = 1;
            table.render();
          }),
      );
      dom.get("headerSearch").addEventListener("input", () => {
        dom.setValue("patientSearch", dom.get("headerSearch").value);
        state.page = 1;
        table.render();
      });
      dom.get("clearFilters").addEventListener("click", search.clear);
      dom.get("emptyClear").addEventListener("click", search.clear);
      dom.get("addPatientButton").addEventListener("click", () => modal.open());
      dom.get("closePatientModal").addEventListener("click", modal.close);
      dom.get("cancelPatient").addEventListener("click", modal.close);
      dom
        .get("patientForm")
        .addEventListener("submit", (event) => form.submit(event));
      dom
        .get("dateOfBirth")
        .addEventListener("input", () =>
          dom.setValue("age", dateUtils.ageFromDob(dom.value("dateOfBirth"))),
        );
      dom
        .get("patientsBody")
        .addEventListener("click", this.handlePatientAction);
      dom.get("pagination").addEventListener("click", this.handlePageChange);
      dom.get("patientModal").addEventListener("click", (event) => {
        if (event.target === dom.get("patientModal")) modal.close();
      });
    },
    handlePatientAction(event) {
      const button = event.target.closest("button[data-action]");
      if (!button) return;

      const patient = patientData.find(button.dataset.id);
      if (!patient) return;
      if (button.dataset.action === "delete") {
        StatusModal.confirm(
          "Delete patient record?",
          `This will remove ${patientData.fullName(patient)}'s record from the patient list.`,
          () => {
            patientData.remove(patient.id);
            table.render();
          },
        );
        return;
      }
      modal.open(patient, button.dataset.action === "view");
    },
    handlePageChange(event) {
      const button = event.target.closest("button[data-page]");
      if (!button || button.disabled) return;
      state.page = Number(button.dataset.page);
      table.render();
    },
  };

  function openSidebar() {
    dom.get("sidebar").classList.add("sidebar-open");
    dom.get("sidebarOverlay").classList.add("active");
  }

  function closeSidebar() {
    dom.get("sidebar").classList.remove("sidebar-open");
    dom.get("sidebarOverlay").classList.remove("active");
  }

  document.addEventListener("DOMContentLoaded", events.bind.bind(events));
  window.openSidebar = openSidebar;
  window.closeSidebar = closeSidebar;
})();
