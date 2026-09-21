<?php
$pageTitle = 'Patients Record';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLSU-MEDIFILE | Patients Record</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/patients.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<div class="dashboard">
    <aside class="sidebar" id="sidebar">

        <div class="brand">
            <div class="brand-logo">
              <img width="50" src="<?= BASE_URL ?>assets/images/SLSU-LOGO.png" alt="SLSU logo">
            </div>

            <div class="brand-text">
              <h1>SLSU-Health Record</h1>
              <p>Medical Records System</p>
            </div>
        </div>

        <div class="admin-profile">
            <div class="admin-avatar">
              AD
            </div>

            <div class="admin-info">
              <strong>
                Administrator
              </strong>

              <span>
                System Administrator
              </span>

              <small>
                <i></i>Online
              </small>
            </div>
        </div>

        <nav class="navigation">
            <p class="nav-label">
              MAIN MENU
            </p>

            <a href="<?= BASE_URL ?>Dashboard" class="nav-item">
              <span class="nav-icon">
                <i class="fa-solid fa-house"></i>
              </span>

              <span>
                Dashboard
              </span>
            </a>

            <a href="<?= BASE_URL ?>Patients" class="nav-item active" aria-current="page">
              <span class="nav-icon">
                <i class="fa-solid fa-user-injured"></i>
              </span>

              <span>
                Patient Records
              </span>
            </a>

            <a href="#" class="nav-item">
              <span class="nav-icon">
                <i class="fa-solid fa-file-medical"></i>
              </span>

              <span>
                Medical Records
              </span>
            </a>

            <a href="#" class="nav-item">
              <span class="nav-icon">
                <i class="fa-solid fa-calendar-check"></i>
              </span>

              <span>
                Appointments
              </span>
            </a>

            <a href="#" class="nav-item">
              <span class="nav-icon">
                <i class="fa-solid fa-user-nurse"></i>
              </span>
              
              <span>
                Users / Staff
              </span>
            </a>

            <a href="#" class="nav-item">
              <span class="nav-icon">
                <i class="fa-solid fa-chart-line"></i>
              </span>
              
              <span>
                Reports &amp; Analytics
              </span>
            </a>

            <a href="#" class="nav-item">
              <span class="nav-icon">
                <i class="fa-solid fa-clock-rotate-left"></i>
              </span>
              
              <span>
                Activity Logs
              </span>
            </a>

            <a href="#" class="nav-item notification-nav">
              <span class="nav-icon">
                <i class="fa-solid fa-bell"></i>
              </span>

              <span>
                Notifications
              </span>
              
              <b>
                3
              </b>
            </a>

            <a href="#" class="nav-item">
              <span class="nav-icon">
                <i class="fa-solid fa-gear"></i>
              </span>
              
              <span>
                Settings
              </span>
            </a>
        </nav>

        <div class="sidebar-bottom">
          <a href="#" class="logout">
            <span class="nav-icon">
              <i class="fa-solid fa-right-from-bracket"></i>
            </span>
            
            <span>
              Logout
            </span>
          </a>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <main class="main">
        <header class="top-header">
            <div class="header-left">
                <button class="mobile-menu" onclick="openSidebar()" aria-label="Open navigation">
                  <i class="fa-solid fa-bars"></i>
                </button>

                <div>
                  <h2>
                    Patients Record
                  </h2>

                  <p>
                    Welcome back, <strong>Administrator</strong>
                  </p>
                </div>
            </div>

            <div class="header-right">
                <div class="search">
                  <span>
                    <i class="fa-solid fa-magnifying-glass"></i>
                  </span>
                  
                  <input id="headerSearch" type="search" placeholder="Search records, patients..." aria-label="Search records and patients">
                </div>

                <button class="header-button notification-button" aria-label="Notifications">
                  <i class="fa-solid fa-bell"></i>
                  <i></i>
                </button>

                <button class="header-profile" aria-label="Administrator profile">
                  <div class="profile-avatar">
                    AD
                  </div>

                  <div class="profile-details">
                    <strong>
                      Administrator
                    </strong>

                    <span>
                      Admin
                    </span>
                  </div>

                  <span class="profile-arrow">
                    <i class="fa-solid fa-chevron-down"></i>
                  </span>

                </button>
            </div>
        </header>

        <div class="content patients-content">
            <section class="page-heading">
                <div>
                  <span class="eyebrow-label">
                    <i class="fa-solid fa-folder-open"></i> Patient directory
                  </span>
                  
                  <h1>
                    Patients Record
                  </h1>
                  <p>
                    Manage and view patient information.
                  </p>
                </div>
                
                <button class="primary-button" id="addPatientButton">
                  <i class="fa-solid fa-plus"></i> Add Patient
                </button>
            </section>

            <section class="card patient-toolbar" aria-label="Patient search and filters">
                <div class="patient-search">
                  <i class="fa-solid fa-magnifying-glass"></i>
                  <input id="patientSearch" type="search" placeholder="Search by name, patient ID, or contact number" aria-label="Search patients">
                </div>

                <label class="filter-field">
                  <span>
                    Gender
                  </span>

                  <select id="genderFilter">
                    <option value="">
                      All genders
                    </option>

                    <option>
                      Female
                    </option>

                    <option>
                      Male
                    </option>

                    <option>
                      Other
                    </option>
                  </select>
                </label>

                <label class="filter-field">
                  <span>
                    Age group
                  </span>

                  <select id="ageFilter">
                    <option value="">
                      All ages
                    </option>

                    <option value="child">
                      Under 18
                    </option>

                    <option value="adult">
                      18-59
                    </option>

                    <option value="senior">
                      60 and above
                    </option>

                  </select>
                </label>

                <label class="filter-field">
                  <span>
                    Status
                  </span>

                  <select id="statusFilter">

                    <option value="">
                      All statuses
                    </option>

                    <option value="Active">
                      Active
                    </option>
                    
                    <option value="Inactive">
                      Inactive
                    </option>
                  </select>
                </label>

                <button class="clear-filter" id="clearFilters" type="button">
                  Clear filters
                </button>
            </section>

            <section class="card patients-card">
                <div class="card-header patients-card-header">
                  <div class="card-title">

                    <div class="title-icon">
                      <i class="fa-solid fa-user-injured"></i>
                    </div>

                    <div>

                      <h3>
                        All Patients
                      </h3>

                      <p id="resultSummary">
                        Showing patient records
                      </p>

                    </div>
                  
                  </div>
                  
                  <span class="record-count" id="recordCount">
                    0 patients
                  </span>

                </div>

                <div class="table-wrapper patient-table-wrapper">

                    <div class="table-loading" id="tableLoading">
                      <span class="spinner"></span>
                      <p>
                        Loading patient records...
                      </p>
                    </div>

                    <table id="patientsTable" aria-describedby="resultSummary">
                      <thead>
                        <tr>
                          <th>Patient ID</th>
                          <th>Patient Name</th>
                          <th>Age</th>
                          <th>Gender</th>
                          <th>Contact Number</th>
                          <th>Date Created</th>
                          <th>Status</th>
                          <th>Actions</th>
                        </tr>
                      </thead>

                      <tbody id="patientsBody"></tbody>
                    </table>

                    <div class="empty-state" id="emptyState" hidden>
                      <div class="empty-icon">
                        <i class="fa-solid fa-user-slash"></i>
                      </div>
                      
                      <h3>
                        No patients found
                      </h3>
                      
                      <p>
                        Try adjusting your search or filters to find a patient record.
                      </p>
                      
                      <button class="view-button" id="emptyClear" type="button">
                        Clear filters
                      </button>
                    </div>

                </div>

                <div class="pagination" id="pagination" aria-label="Patient records pagination"></div>
            </section>
        </div>
    </main>
</div>

<div class="patient-modal-backdrop" id="patientModal" hidden>

    <section class="patient-modal" role="dialog" aria-modal="true" aria-labelledby="patientModalTitle">

        <div class="patient-modal-header">
          <div>
            <span class="eyebrow-label">
              <i class="fa-solid fa-clipboard-user"></i> Patient record
            </span>
            
            <h2 id="patientModalTitle">
              Add Patient Record
            </h2>
            
            <p id="patientModalDescription">
              Enter the patient's information below.
            </p>
          
          </div>
            <button class="modal-close" id="closePatientModal" type="button" aria-label="Close form">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

        
          <form id="patientForm" class="patientForm" novalidate>

            <div id="formPage1" class="form-page-1 form-page">

              <div class="main-form-wrapper">


                <div class="container-wrapper">

                  <div class="info-container">

                    <input type="hidden" id="patientId" name="patientId">

                    <input type="hidden" id="examinationId" name="examinationId">

                    <div class="form-section">
                      <h3>
                        Basic information
                      </h3>

                      <div class="form-grid">
                        
                        <label class="form-field patient-id" style="display: none;">
                          <span>Patient ID</span>
                          <input style="text-align: center;" id="patientIdDisplay" type="hidden" readonly>
                        </label>
                        
                        <div class="form-field-group">

                          <label class="form-field required">
                            <span>Family Name</span>
                            <input id="surName" name="surName" type="text" autocomplete="name" required>
                            <small class="field-error"></small>
                          </label>

                          <label class="form-field required">
                            <span>Given Name</span>
                            <input id="firstName" name="firstName" type="text" autocomplete="name" required>
                            <small class="field-error"></small>
                          </label>

                          <label class="form-field required">
                            <span>Middle Name</span>
                            <input id="middleName" name="middleName" type="text" autocomplete="name" required>
                            <small class="field-error"></small>
                          </label>

                        </div>
                        
                        <label class="form-field required">
                          <span>Date of Birth</span>
                          <input id="dateOfBirth" name="dateOfBirth" type="date" required>
                          <small class="field-error"></small>
                        </label>
                        
                        <label class="form-field">
                          <span>Age</span>
                          <input id="age" name="age" type="text" readonly placeholder="Calculated automatically">
                        </label>
                        
                        <label class="form-field required">
                          <span>Sex</span>
                          <select id="gender" name="gender" required>
                            <option value="" disabled hidden selected>Select gender</option>
                            <option value="Female">Female</option>
                            <option value="Male">Male</option>
                          </select>
                          <small class="field-error"></small>
                        </label>

                        <label class="form-field required">
                          <span>Civil Status</span>
                          <select id="civilStatus" name="civilStatus" required>
                            <option value="" disabled hidden selected>Select status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Separated">Separated</option>
                            <option value="Annulled">Annulled</option>
                          </select>
                          <small class="field-error"></small>
                        </label>

                        <label class="form-field required">
                          <span>Religion</span>
                          <input id="religion" name="religion" type="text" autocomplete="religion"  required>
                          <small class="field-error"></small>
                        </label>

                        <label class="form-field required">
                          <span>Nationality</span>
                          <input id="nationality" name="nationality" type="text" autocomplete="nationality"  required>
                          <small class="field-error"></small>
                        </label>
                          

                        <label class="form-field required">
                          <span>College/Dept</span>
                          <input id="department" name="department" type="text" autocomplete="department"  required>
                          <small class="field-error"></small>
                        </label>

                        <label class="form-field required">
                          <span>Job Position/Course</span>
                          <input id="position" name="position" type="text" autocomplete="position"  required>
                          <small class="field-error"></small>
                        </label>

                        <label class="form-field required">
                          <span>Contact Number</span>
                          <input id="contactNumber" name="contactNumber" type="tel" autocomplete="tel" placeholder="09XX XXX XXXX"
                          maxlength="11" required>
                          <small class="field-error"></small>
                        </label>
                        
                        <label class="form-field">
                          <span>Address</span>
                          <input id="address" name="address" type="text" autocomplete="street-address">
                        </label>

                      </div>

                    </div>

                    <div class="form-section emergency-container">

                      <div class="emergency-header">

                        <i class="fas fa-truck-medical"></i>

                        <h3>In case of emergency</h3>

                      </div>

                      <div class="form-grid">
                        <label class="form-field full-field required">
                          <span>Emergency Contact Name</span>
                          <input id="emergencyName" name="emergencyName" type="text"
                          required>
                          <small class="field-error"></small>
                        </label>
                        
                        <label class="form-field full-field required">
                          <span>Emergency Contact Number</span>
                          <input id="emergencyNumber" name="emergencyNumber" type="tel" maxlength="11" required>
                          <small class="field-error"></small>
                        </label>
                        
                        <label class="form-field full-field required">
                          <span>Emergency Address</span>
                          <input id="emergencyAddress" name="emergencyAddress" type="tel" required>
                          <small class="field-error"></small>
                        </label>
                      </div>
                    </div>

                  </div>

                  
                  <div class="form-section past-history">

                    <h3>
                      Past Medical / Family History
                    </h3>

                    <div class="form-grid">

                      <!-- COLUMN 1 -->
                      <div class="checkbox-column">

                        <div class="checkbox">
                          <input
                            type="checkbox"
                            name="history"
                            value="Hypertension"
                            id="hypertension"
                          >
                          <label for="hypertension">Hypertension</label>
                        </div>

                        <div class="checkbox">
                          <input
                            type="checkbox"
                            name="history"
                            value="Heart Disease"
                            id="heartDisease"
                          >
                          <label for="heartDisease">Heart Disease</label>
                        </div>

                        <div class="checkbox">
                          <input
                            type="checkbox"
                            name="history"
                            value="Astma"
                            id="asthma"
                          >
                          <label for="asthma">Asthma</label>
                        </div>

                        <div class="checkbox">
                          <input
                            type="checkbox"
                            name="history"
                            value="Lung Disease"
                            id="lungDisease"
                          >
                          <label for="lungDisease">Lung Disease</label>
                        </div>

                        <div class="checkbox">
                          <input
                            type="checkbox"
                            name="history"
                            value="Malignancy"
                            id="malignancy"
                          >
                          <label for="malignancy">Malignancy</label>
                        </div>

                      </div>


                      <!-- COLUMN 2 -->
                      <div class="checkbox-column">

                        <div class="checkbox">
                          <input
                            type="checkbox"
                            name="history"
                            value="Allergy"
                            id="allergy"
                          >
                          <label for="allergy">Allergy</label>
                        </div>

                        <div class="checkbox">
                          <input
                            type="checkbox"
                            name="history"
                            value="Goiter"
                            id="goiter"
                          >
                          <label for="goiter">Goiter</label>
                        </div>

                        <div class="checkbox">
                          <input
                            type="checkbox"
                            name="history"
                            value="Diabetes"
                            id="diabetes"
                          >
                          <label for="diabetes">Diabetes</label>
                        </div>

                        <div class="checkbox">
                          <input
                            type="checkbox"
                            name="history"
                            value="Bleeding Tendency"
                            id="bleedingTendency"
                          >
                          <label for="bleedingTendency">Bleeding Tendency</label>
                        </div>

                        <div class="checkbox">
                          <input
                            type="checkbox"
                            name="history"
                            value="Hernia"
                            id="hernia"
                          >
                          <label for="hernia">Hernia</label>
                        </div>

                      </div>


                      <!-- COLUMN 3 -->
                      <div class="checkbox-column">

                        <div class="checkbox">
                          <input
                            type="checkbox"
                            name="history"
                            value="Liver Disease"
                            id="liverDisease"
                          >
                          <label for="liverDisease">Liver Disease</label>
                        </div>

                        <div class="checkbox">
                          <input
                            type="checkbox"
                            name="history"
                            value="Gall Bladder Disease"
                            id="gallBladderDisease"
                          >
                          <label for="gallBladderDisease">Gall Bladder Disease</label>
                        </div>

                        <div class="checkbox">
                          <input
                            type="checkbox"
                            name="history"
                            value="Ulcer"
                            id="ulcer"
                          >
                          <label for="ulcer">Ulcer</label>
                        </div>

                        <div class="checkbox">
                          <input
                            type="checkbox"
                            name="history"
                            value="Renal Disease"
                            id="renalDisease"
                          >
                          <label for="renalDisease">Renal Disease</label>
                        </div>

                        <div class="checkbox">
                          <input
                            type="checkbox"
                            name="history"
                            value="Hernia"
                            id="hernia2"
                          >
                          <label for="hernia2">Hernia</label>
                        </div>

                      </div>

                      

                    </div> <!-- END form-grid -->

                    <div class="divider" style="margin: 0.74rem 0;"></div>

                    <div class="other-container">

                      <h3>Others:</h3>

                      <div class="form-grid">

                        <div class="form-field-group-1">
                        
                          <label class="form-field">
                            <span>Previous Hospitalization</span>
                            <input id="previousHospitalization" type="text">
                          </label>

                          <label class="form-field">
                            <span>Previous Operation</span>
                            <input id="previousOperation" type="text">
                          </label>

                          <label class="form-field">
                            <span>Previous Trauma</span>
                            <input id="previousTrauma" type="text">
                          </label>

                        </div>

                      </div>

                      <div class="history-container" style="margin-top: 0.75rem;">

                        <h3>History of:</h3>

                        <div class="form-grid">
                          
                          <div class="form-field-group-3">

                            <div class="checkbox">
                              <input
                                type="checkbox"
                                name="socialHistory"
                                value="Smoking"
                                id="smoking"
                              >
                              <label for="liverDisease">Smoking</label>
                            </div>

                            <div class="checkbox">
                              <input
                                type="checkbox"
                                name="socialHistory"
                                value="Alcoholism"
                                id="alcoholism"
                              >
                              <label for="gallBladderDisease">Alcoholism</label>
                            </div>

                            <div class="checkbox">
                              <input
                                type="checkbox"
                                name="socialHistory"
                                value="Sports"
                                id="sports"
                              >
                              <label for="sports">Sports</label>
                            </div>

                          </div>

                          <div class="form-field-group-1">

                            <label class="form-field">
                              <span>Specify:</span>
                              <input id="sportsDefinition" type="text">
                            </label>
                          </div>

                        </div>

                      </div>

                    </div>

                  </div>


                </div>

                <div class="physical-examination-container">

                  <div class="form-section">

                    <h3>
                      Physical Examination
                    </h3>

                    <div class="form-grid">

                      <div class="form-field-group-4">

                        <label class="form-field">
                          <span>Blood Pressure</span>
                          <div class="field-span">
                            <input id="bloodPressure" name="bloodPressure" type="text">
                            <span class="sub-label">mmHg</span>
                          </div>
                        </label>

                        <label class="form-field">
                          <span>Temperature</span>
                          <div class="field-span">
                            <input id="temp" name="temp" type="text">
                            <span class="sub-label">°C</span>
                          </div>
                        </label>

                        <label class="form-field">
                          <span>Pulse Rate</span>
                          <div class="field-span">
                            <input id="pulse" name="pulse" type="text">
                            <span class="sub-label">bpm</span>
                          </div>
                        </label>

                        <label class="form-field">
                          <span>Resp. Rate</span>
                          <div class="field-span">
                            <input id="respRate" name="respRate" type="text">
                            <span class="sub-label">/min</span>
                          </div>
                        </label>

                      </div>


                      <div class="form-field-group-3-standalone">

                        <label class="form-field">
                          <span>Height</span>
                          <div class="field-span">
                            <input id="height" name="height" type="text">
                            <span class="sub-label">cm</span>
                          </div>
                        </label>

                        <label class="form-field">
                          <span>Weight</span>
                          <div class="field-span">
                            <input id="weight" name="weight" type="text">
                            <span class="sub-label">kg</span>
                          </div>
                        </label>

                        <label class="form-field">
                          <span>Kg. Ideal Body Weight</span>
                          <div class="field-span">
                            <input id="idealWeight" name="idealWeight" type="text" readonly placeholder="Calculated Automatically">
                            <span class="sub-label">kg</span>
                          </div>
                        </label>

                      </div>
                      
                      <div class="divider"></div>

                      <div class="form-field-group-1">

                        <label class="form-field">
                          <span>HEAD / NECK</span>
                          <div class="field-span">
                            <input id="headNeck" name="headNeck" type="text">
                          </div>
                        </label>

                        <label class="form-field">
                          <span>RESPIRATORY</span>
                          <div class="field-span">
                            <input id="respiratory" name="respiratory" type="text">
                          </div>
                        </label>

                        <label class="form-field">
                          <span>CARDIO-VASCULAR</span>
                          <div class="field-span">
                            <input id="cardioVascular" name="cardioVascular" type="text">
                          </div>
                        </label>

                        <label class="form-field">
                          <span>GASTRO-INTESTINAL</span>
                          <div class="field-span">
                            <input id="gastroIntestinal" name="gastroIntestinal" type="text">
                          </div>
                        </label>

                        <label class="form-field">
                          <span>GENITO-URINARY</span>
                          <div class="field-span">
                            <input id="genitoUrinary" name="genitoUrinary" type="text">
                          </div>
                        </label>
                        
                        <label class="form-field">
                          <span>EXTREMITIES</span>
                          <div class="field-span">
                            <input id="extremities" name="extremities" type="text">
                          </div>
                        </label>

                        <label class="form-field">
                          <span>NEUROLOGIC</span>
                          <div class="field-span">
                            <input id="neurologic" name="neurologic" type="text">
                          </div>
                        </label>


                      </div>

                      <div class="form-field-group-2 suggestion-container-wrapper">

                        <div class="suggestion-conatiner">

                          <h3 class="suggestion-header">
                            <span class="icon">
                              <i class="fas fa-microscope"></i>
                            </span>
                            <span class="sub-text">
                              Diagnosis / Treatment / Suggestions
                            </span>
                              
                          </h3>

                          <label class="form-field">
                            <div class="field-span ">
                              <textarea name="suggestion" id="suggestion" class="long-text-field"
                              wrap="off">

                              </textarea>
                            </div>
                          </label>

                        </div>

                        <div class="suggestion-container">


                          <h3 class="laboratory-header">
                            <span class="icon">
                              <i class="fas fa-user-doctor"></i>
                            </span>
                            <span class="sub-text">
                              Laboratory
                            </span>
                          </h3>

                          <label class="form-field">
                            <div class="field-span ">
                              <textarea name="laboratory" id="laboratory" class="long-text-field"
                              wrap="off">

                              </textarea>
                            </div>
                          </label>

                        </div>

                      </div>


                    </div>


                  </div>

                </div>

              </div>

              <div class="form-actions">
                <button class="cancel-button" id="cancelPatient" type="button">Cancel</button>
                <button class="primary-button" id="next" type="button">
                  Next
                  <i class="fa-solid fa-arrow-right"></i>
                </button>
              </div>

            </div>


            <div id="formPage2" class="form-page-2 form-page" style="display: none;">

              <div class="page2-main-wrapper">

                <div class="dental-container">

                  <div class="dental-header">
                    <i class="fa-solid fa-tooth"></i>
                    <span>Dental Record</span>
                  </div>

                  <div class="dental-grid">

                    <div class="legend-container">
                      <h3>Legend:</h3>

                      <div class="legend">

                        <div class="legend-item-container">

                          <div class="legend-item">
                              <span class="legend-icon legend-icon-caries">
                                  <i class="fa-solid fa-tooth caries"></i>
                              </span>

                              <div class="legend-content">
                                  <p class="legend-code"><strong> C - </strong></p>
                                  <p class="legend-label">Caries</p>
                              </div>
                          </div>

                          <div class="legend-item">
                              <span class="legend-icon legend-icon-caries">
                                  <i class="fa-solid fa-tooth caries"></i>
                              </span>

                              <div class="legend-content">
                                  <p class="legend-code"><strong> C1 - </strong></p>
                                  <p class="legend-label">Vital Exposed</p>
                              </div>
                          </div>

                          <div class="legend-item">
                              <span class="legend-icon legend-icon-caries">
                                  <i class="fa-solid fa-tooth caries"></i>
                              </span>

                              <div class="legend-content">
                                  <p class="legend-code"><strong> C2 - </strong></p>
                                  <p class="legend-label">Non-Vital Pulp Exposed</p>
                              </div>
                          </div>

                          <div class="legend-item">
                              <span class="legend-icon legend-icon-caries">
                                  <i class="fa-solid fa-tooth caries"></i>
                              </span>

                              <div class="legend-content">
                                  <p class="legend-code"><strong> X - </strong></p>
                                  <p class="legend-label">Indicated for Extraction</p>
                              </div>
                          </div>

                          <div class="legend-item">
                              <span class="legend-icon legend-icon-caries">
                                  <i class="fa-solid fa-tooth caries"></i>
                              </span>

                              <div class="legend-content">
                                  <p class="legend-code"><strong> RF - </strong></p>
                                  <p class="legend-label">Retained Root Fragment</p>
                              </div>
                          </div>

                          <div class="legend-item">
                              <span class="legend-icon legend-icon-caries">
                                  <i class="fa-solid fa-tooth caries"></i>
                              </span>

                              <div class="legend-content">
                                  <p class="legend-code"><strong> AM - </strong></p>
                                  <p class="legend-label">Amalgam Filling</p>
                              </div>
                          </div>

                          <div class="legend-item">
                              <span class="legend-icon legend-icon-caries">
                                  <i class="fa-solid fa-tooth caries"></i>
                              </span>

                              <div class="legend-content">
                                  <p class="legend-code"><strong> S - </strong></p>
                                  <p class="legend-label">Silicate Filling</p>
                              </div>
                          </div>

                          <div class="legend-item">
                              <span class="legend-icon legend-icon-caries">
                                  <i class="fa-solid fa-tooth caries"></i>
                              </span>

                              <div class="legend-content">
                                  <p class="legend-code"><strong> GC - </strong></p>
                                  <p class="legend-label">Gold Crown</p>
                              </div>
                          </div>

                          <div class="legend-item">
                              <span class="legend-icon legend-icon-caries">
                                  <i class="fa-solid fa-tooth caries"></i>
                              </span>

                              <div class="legend-content">
                                  <p class="legend-code"><strong> AB - </strong></p>
                                  <p class="legend-label">Bridge Abutment</p>
                              </div>
                          </div>

                          <div class="legend-item">
                              <span class="legend-icon legend-icon-caries">
                                  <i class="fa-solid fa-tooth caries"></i>
                              </span>

                              <div class="legend-content">
                                  <p class="legend-code"><strong> P - </strong></p>
                                  <p class="legend-label">Pontic</p>
                              </div>
                          </div>

                          <div class="legend-item">
                              <span class="legend-icon legend-icon-caries">
                                  <i class="fa-solid fa-tooth caries"></i>
                              </span>

                              <div class="legend-content">
                                  <p class="legend-code"><strong> U - </strong></p>
                                  <p class="legend-label">Gold Clasp</p>
                              </div>
                          </div>

                          <div class="legend-item">
                              <span class="legend-icon legend-icon-caries">
                                  <i class="fa-solid fa-tooth caries"></i>
                              </span>

                              <div class="legend-content">
                                  <p class="legend-code"><strong> GI - </strong></p>
                                  <p class="legend-label">Gold Inlay</p>
                              </div>
                          </div>

                          <div class="legend-item">
                              <span class="legend-icon legend-icon-caries">
                                  <i class="fa-solid fa-tooth caries"></i>
                              </span>

                              <div class="legend-content">
                                  <p class="legend-code"><strong> M - </strong></p>
                                  <p class="legend-label">Missing due to Extraction</p>
                              </div>
                          </div>

                        </div>

                      </div>

                    </div>

                    <div class="dental-form">
                      
                        <div class="upper-container">

                          <div class="right-label">
                            RIGHT UPPER
                          </div>

                          <div class="upper-teeth">

                            <div class="upper-teeth-item">
                              <label for="t1">
                                1
                              </label>

                              <input type="text" name="t1" id="t1" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="upper-teeth-item">
                              <label for="t2">
                                2
                              </label>

                              <input type="text" name="t2" id="t2" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="upper-teeth-item">
                              <label for="t3">
                                3
                              </label>

                              <input type="text" name="t3" id="t3" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="upper-teeth-item">
                              <label for="t4">
                                4
                              </label>

                              <input type="text" name="t4" id="t4" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="upper-teeth-item">
                              <label for="t5">
                                5
                              </label>

                              <input type="text" name="t5" id="t5" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="upper-teeth-item">
                              <label for="t6">
                                6
                              </label>

                              <input type="text" name="t6" id="t6" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="upper-teeth-item">
                              <label for="t7">
                                7
                              </label>

                              <input type="text" name="t7" id="t7" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="upper-teeth-item">
                              <label for="t8">
                                8
                              </label>

                              <input type="text" name="t8" id="t8" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="upper-teeth-item">
                              <label for="t9">
                                9
                              </label>

                              <input type="text" name="t9" id="t9" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="upper-teeth-item">
                              <label for="t10">
                                10
                              </label>

                              <input type="text" name="t10" id="t10" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="upper-teeth-item">
                              <label for="t11">
                                11
                              </label>

                              <input type="text" name="t11" id="t11" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="upper-teeth-item">
                              <label for="t12">
                                12
                              </label>

                              <input type="text" name="t12" id="t12" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="upper-teeth-item">
                              <label for="t13">
                                13
                              </label>

                              <input type="text" name="t13" id="t13" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="upper-teeth-item">
                              <label for="t14">
                                14
                              </label>

                              <input type="text" name="t14" id="t14" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="upper-teeth-item">
                              <label for="t15">
                                15
                              </label>

                              <input type="text" name="t15" id="t15" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="upper-teeth-item">
                              <label for="t16">
                                16
                              </label>

                              <input type="text" name="t16" id="t16" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                          </div>

                          <div class="left-label">
                            LEFT UPPER
                          </div>

                        </div>

                        <div class="dental-divider"></div>

                        <div class="lower-container">

                          <div class="right-label">
                            RIGHT LOWER
                          </div>

                          <div class="lower-teeth">

                            <div class="lower-teeth-item">
                              <label for="t32">
                                32
                              </label>

                              <input type="text" name="t32" id="t32" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="lower-teeth-item">
                              <label for="t31">
                                31
                              </label>

                              <input type="text" name="t31" id="t31" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="lower-teeth-item">
                              <label for="t30">
                                30
                              </label>

                              <input type="text" name="t30" id="t30" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="lower-teeth-item">
                              <label for="t29">
                                29
                              </label>

                              <input type="text" name="t29" id="t29" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="lower-teeth-item">
                              <label for="t28">
                                28
                              </label>

                              <input type="text" name="t28" id="t28" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="lower-teeth-item">
                              <label for="t27">
                                27
                              </label>

                              <input type="text" name="t27" id="t27" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="lower-teeth-item">
                              <label for="t26">
                                26
                              </label>

                              <input type="text" name="t26" id="t26" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="lower-teeth-item">
                              <label for="t25">
                                25
                              </label>

                              <input type="text" name="t25" id="t25" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="lower-teeth-item">
                              <label for="t24">
                                24
                              </label>

                              <input type="text" name="t24" id="t24" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="lower-teeth-item">
                              <label for="t23">
                                23
                              </label>

                              <input type="text" name="t23" id="t23" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="lower-teeth-item">
                              <label for="t22">
                                22
                              </label>

                              <input type="text" name="t22" id="t22" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="lower-teeth-item">
                              <label for="t21">
                                21
                              </label>

                              <input type="text" name="t21" id="t21" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="lower-teeth-item">
                              <label for="t20">
                                20
                              </label>

                              <input type="text" name="t20" id="t20" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="lower-teeth-item">
                              <label for="t19">
                                19
                              </label>

                              <input type="text" name="t19" id="t19" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="lower-teeth-item">
                              <label for="t18">
                                18
                              </label>

                              <input type="text" name="t18" id="t18" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                            <div class="lower-teeth-item">
                              <label for="t17">
                                17
                              </label>

                              <input type="text" name="t17" id="t17" class="dental-field" maxlength="2">

                              <span>
                                <i class="fa-solid fa-tooth"></i>
                              </span>
                            </div>

                          </div>

                          <div class="left-label">
                            LEFT LOWER
                          </div>

                        </div>

                    </div>

                  </div> 

                </div>

              </div>

              <div class="form-actions">
                <button class="previous-button" id="previous" type="button">
                    <i class="fa-solid fa-arrow-left"></i>
                    Previous
                </button>
                <button class="primary-button" id="savePatient" type="submit">
                  <i class="fa-solid fa-check"></i> Save Patient
                </button>
              </div>

            </div>

          </form>

    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script src="<?= BASE_URL ?>assets/components/modal.js"></script>
<script src="<?= BASE_URL ?>assets/js/patients/index.js" type="module"></script>
</body>
</html>
