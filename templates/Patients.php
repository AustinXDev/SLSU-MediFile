<?php
require_once __DIR__ . '/../config/init.php';

use App\Middleware\AdminMiddleware;
use App\Session\SessionManager;

$session = new SessionManager();
$middleware = new AdminMiddleware($session);

$middleware->requireAuth();

$role = trim((string) ($session->get('role') ?? ''));

$pageTitle = 'Patients Record';

$username = (string) ($session->get('admin_username') ?? '');
$roleName = (string) ($session->get('role') ?? '');
$initials = strtoupper(substr($username !== '' ? $username : 'A', 0, 2));

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
                <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>
              </strong>

              <span>
                <?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8') ?>
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

            <a href="dashboard" class="nav-item">
              <span class="nav-icon">
                <i class="fa-solid fa-house"></i>
              </span>

              <span>
                Dashboard
              </span>
            </a>

            <a href="patients" class="nav-item active" aria-current="page">
              <span class="nav-icon">
                <i class="fa-solid fa-user-injured"></i>
              </span>

              <span>
                Patient Records
              </span>
            </a>

            <?php
                if (strtolower(trim($role)) === 'super admin'):
                    ?>

            <a href="users" class="nav-item">
              <span class="nav-icon">
                <i class="fa-solid fa-user-nurse"></i>
              </span>
              
              <span>
                Users / Staff
              </span>
            </a>

            <?php endif; ?>
            
            <?php
                    if (strtolower(trim($role)) === 'super admin' || strtolower(trim($role)) === 'administrator'):
                        ?>

            <a href="csmdashboard" class="nav-item">
              <span class="nav-icon">
                <i class="fa-solid fa-chart-line"></i>
              </span>
              
              <span>
                Reports &amp; Analytics
              </span>
            </a>

            <?php endif; ?>
            
            <?php
            if (strtolower(trim($role)) === 'super admin'):
                ?>

            <a href="<?= BASE_URL ?>activitylogs" class="nav-item">
              <span class="nav-icon">
                <i class="fa-solid fa-clock-rotate-left"></i>
              </span>
              
              <span>
                Activity Logs
              </span>
            </a>

            <?php endif; ?>

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
                <button class="header-button notification-button" aria-label="Notifications">
                  <i class="fa-solid fa-bell"></i>
                  <i></i>
                </button>

                <button class="header-profile" aria-label="Administrator profile">
                  <div class="profile-avatar">
                    <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?>
                  </div>

                  <div class="profile-details">
                      <strong><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></strong>
                      <span><?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8') ?></span>
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
                  <input id="patientSearch" type="search" placeholder="Search by name, student ID, or contact number" aria-label="Search patients">
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
                      18-40
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
                          <th>Student ID</th>
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

<!-- Form Modal -->
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

                        <div class="form-field-group-1">
                          <label for="form-field required" class="form-field">
                            <span>Student ID</span>
                            <input style="text-align: center;" id="StudentId" type="text">
                            <small class="field-error"></small>
                          </label>
                        </div>
                        
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
                            value="Immune Disease"
                            id="immuneDisease"
                          >
                          <label for="hernia2">Immune Disease</label>
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
                              wrap="soft">

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
                              wrap="soft">

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

                <input type="text" class="hidden" id="dentalId">

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

                  <div class="treatment-plan">

                    <div class="treatment-plan-header">
                      <h3>Treatment Plan</h3>
                    </div>

                    <div class="treatment-plan-list">

                      <div class="treatment-row">
                        <div class="clinic-name">
                          <span>Oral Surgery Clinic</span>
                        </div>

                        <div class="treatment-field">
                          <textarea name="treatment_plan[oral_surgery]" id="oralSurgery" class="treatment-plan-field" rows="2"></textarea>
                        </div>
                      </div>

                      <div class="treatment-row">
                        <div class="clinic-name">
                          <span>Operative Clinic</span>
                        </div>

                        <div class="treatment-field">
                          <textarea name="treatment_plan[operative]" id="operative" class="treatment-plan-field"  rows="2"></textarea>
                        </div>
                      </div>

                      <div class="treatment-row">
                        <div class="clinic-name">
                          <span>Prosthodontia Clinic</span>
                        </div>

                        <div class="treatment-field">
                          <textarea name="treatment_plan[prosthodontia]" id="prosthodontia" class="treatment-plan-field"  rows="2"></textarea>
                        </div>
                      </div>

                      <div class="treatment-row">
                        <div class="clinic-name">
                          <span>Crown & Bridge Clinic</span>
                        </div>

                        <div class="treatment-field">
                          <textarea name="treatment_plan[crown_bridge]" id="crownBridge" class="treatment-plan-field"  rows="2"></textarea>
                        </div>
                      </div>

                      <div class="treatment-row">
                        <div class="clinic-name">
                          <span>Oral Medicine Clinic</span>
                        </div>

                        <div class="treatment-field">
                          <textarea name="treatment_plan[oral_medicine]" id="oralMedicine" class="treatment-plan-field"  rows="2"></textarea>
                        </div>
                      </div>

                      <div class="treatment-row">
                        <div class="clinic-name">
                          <span>X-Ray Clinic</span>
                        </div>

                        <div class="treatment-field">
                          <textarea name="treatment_plan[x_ray]" id="xRay" class="treatment-plan-field"  rows="2"></textarea>
                        </div>
                      </div>

                      <div class="treatment-row">
                        <div class="clinic-name">
                          <span>Children's Clinic</span>
                        </div>

                        <div class="treatment-field">
                          <textarea name="treatment_plan[children]" id="children" class="treatment-plan-field"  rows="2"></textarea>
                        </div>
                      </div>

                      <div class="treatment-row">
                        <div class="clinic-name">
                          <span>Orthodontia Clinic</span>
                        </div>

                        <div class="treatment-field">
                          <textarea name="treatment_plan[orthodontia]" id="orthodontia" class="treatment-plan-field"  rows="2"></textarea>
                        </div>
                      </div>

                      <div class="treatment-row">
                        <div class="clinic-name">
                          <span>Clinical Laboratory</span>
                        </div>

                        <div class="treatment-field">
                          <textarea name="treatment_plan[clinical_lab]" id="clinical" class="treatment-plan-field"  rows="2"></textarea>
                        </div>
                      </div>

                    </div>

                  </div>

                  <div class="service-history-container">

                    <div class="service-history-header">

                      <div class="service-history-head">
                        <h3>
                          Service History
                        </h3>
                      </div>

                      <div class="button-container">
                        <button
                          type="button"
                          class="add-service-button primary-button"
                          data-action="addService"
                          data-add-service
                          id="addServiceBtn">
                          <i class="fa-solid fa-clipboard-list"></i>
                          Add Service
                        </button>
                      </div>

                      <div class="history-container">

                        <table id="serviceHistoryTable" class="service-history-table">
                          
                          <thead>
                            <colgroup>
                              <col class="col-date">
                              <col class="col-service">
                              <col class="col-patient">
                              <col class="col-dentist">
                              <col class="col-actions">
                            </colgroup>

                            <tr>
                              <th>Date</th>
                              <th>Service Rendered</th>
                              <th>Patient's Signature</th>
                              <th>Dentist's Signature</th>
                              <th>Actions</th>
                            </tr>
                          </thead>

                          <tbody id="serviceHistoryBody">
                            
                          </tbody>

                        </table>

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

          <!-- ADD SERVICE MODAL -->
          <div
            id="addServiceModal"
            class="service-modal"
          >
            <div class="service-modal-overlay" data-close-service-modal></div>

            <div
              class="service-modal-dialog"
              role="dialog"
              aria-modal="true"
              aria-labelledby="addServiceModalTitle"
            >

              <!-- Header -->
              <div class="service-modal-header">

                <div class="service-modal-title">
                  <div class="service-modal-icon">
                    <i class="fa-solid fa-clipboard-list"></i>
                  </div>

                  <div>
                    <h2 id="addServiceModalTitle">
                      Add Dental Service
                    </h2>

                    <p>
                      Record a service performed for this patient.
                    </p>
                  </div>
                </div>

                <button
                  type="button"
                  id="closeServiceModal"
                  class="service-modal-close"
                  data-close-service-modal
                  aria-label="Close modal"
                >
                  <i class="fa-solid fa-xmark"></i>
                </button>

              </div>


              <!-- Body -->
                <div class="service-modal-body">
                  

                  <!-- Service Date -->
                  <div class="service-form-group">
                    <label for="serviceDate">
                      Service Date
                    </label>

                    <div class="service-input-icon">
                      <i class="fa-regular fa-calendar"></i>

                      <input
                        type="date"
                        id="serviceDate"
                        name="serviceDate"
                      >
                    </div>
                  </div>


                  <!-- Service Rendered -->
                  <div class="service-form-group">

                    <label for="serviceRendered">
                      Service Rendered
                    </label>

                    <textarea
                      id="serviceRendered"
                      name="serviceRendered"
                      rows="3"
                      placeholder="Describe the dental service performed..."
                    ></textarea>

                  </div>


                  <!-- Patient Acknowledgment -->
                  <div class="service-form-group">

                    <label for="patientSignature">
                      Patient's Signature
                    </label>

                    <div class="signature-pad-wrapper">
                        <canvas id="patientSignature"></canvas>
                    </div>

                    <div class="signature-actions">
                        <button
                            type="button"
                            id="clearPatientSignature"
                            class="signature-clear-button"
                        >
                            <i class="fa-solid fa-eraser"></i>
                            Clear
                        </button>
                    </div>

                  </div>


                  <!-- Dentist -->
                  <div class="service-form-group">

                    <label for="dentistSignature">
                      Dentist
                    </label>

                    <div class="service-readonly-field">

                      <i class="fa-solid fa-user-doctor"></i>

                      <span id="serviceDentistName">
                        Current dentist
                      </span>

                      <span class="auto-label">
                        Automatic
                      </span>

                    </div>

                  </div>

                </div>


              <!-- Footer -->
              <div class="service-modal-footer">

                <button
                  type="button"
                  class="secondary-button"
                  data-close-service-modal
                >
                  Cancel
                </button>

                <button
                  type="button"
                  class="primary-button"
                  id="saveServiceBtn"
                >
                  <i class="fa-solid fa-check"></i>
                  Save Service
                </button>

              </div>

            </div>
          </div>

    </section>
</div>


<!-- VIEW MODAL-->
<div class="modal-overlay" id="openDocumentPreview">
  <div class="document-modal">

    <div class="document-toolbar">
      <div class="toolbar-title">Document Preview</div>
      <div class="toolbar-actions">
        <button class="toolbar-btn" type="button" id="downloadDocument">
          <span class="icon">&#8681;</span>
          <span class="btn-label">Download</span>
        </button>

        <button class="toolbar-btn" type="button" id="printDocument">
          <span class="icon">&#128438;</span>
          <span class="btn-label">Print</span>
        </button>

        <button class="toolbar-btn" type="button" id="closeDocument">
          <span class="icon">X</span>
          <span class="btn-label">Close</span>
        </button>
      </div>
    </div>

    <div class="document-scroll">

      <div class="document">
      
        <!-- ============ PAGE 1 ============ -->
        <div class="document-page-wrapper">
          <div class="document-page">

            <header class="doc-header">
              <div class="seal">
                <div class="seal-circle">
                  <img src="<?= BASE_URL ?>assets/images/SLSU-LOGO.png" alt="slsu-logo">
                </div>
              </div>
              <div class="header-text">
                <p class="republic">Republic of the Philippines</p>
                <h1 class="university">SOUTHERN LUZON STATE UNIVERSITY</h1>
                <p class="uhs">University Health Services (UHS)</p>
                <p class="location">Lucban, Quezon</p>
              </div>
            </header>

            <h2 class="doc-title">MEDICAL RECORD</h2>

            <section class="field-block">
              <div class="line-row">
                <span class="line-label">NAME:</span>
                <span class="dotted-fill" style="display: flex;">
                  <span style="flex: 1; text-align: center;" id="documentLastName"></span>
                  <span style="flex: 1; text-align: center;" id="documentFirstName"></span>
                  <span style="flex: 1; text-align: center;" id="documentMiddleName"></span>
                </span>
              </div>
              <div class="sub-labels three-col">
                <span>Family Name</span>
                <span>Given Name</span>
                <span>Middle Name</span>
              </div>

              <div class="line-row three-part">
                <span class="line-label">BIRTHDAY:</span>
                <span class="dotted-fill short" style="text-align: center;" id="documentBirthday"></span>
                <span class="line-label mid">AGE:</span>
                <span class="dotted-fill short" style="text-align: center;" id="documentAge"></span>
                <span class="line-label mid">SEX:</span>
                <span class="dotted-fill short" style="text-align: center;" id="documentSex"></span>
              </div>

              <div class="line-row three-part">
                <span class="line-label">CIVIL STATUS:</span>
                <span class="dotted-fill short" style="text-align: center;" id="documentCivilStatus"></span>
                <span class="line-label mid">RELIGION:</span>
                <span class="dotted-fill short" style="text-align: center;" id="documentReligion"></span>
                <span class="line-label mid">NATIONALITY:</span>
                <span class="dotted-fill short" style="text-align: center;" id="documentNationality"></span>
              </div>

              <div class="line-row two-part">
                <span class="line-label">COLLEGE/DEPT.:</span>
                <span class="dotted-fill short document-value" style="text-align: center;" id="documentDept"></span>
                <span class="line-label mid">JOB POSITION/ COURSE:</span>
                <span class="dotted-fill" style="text-align: center;" id="documentCourse"></span>
              </div>

              <div class="line-row two-part">
                <span class="line-label">HOME ADDRESS:</span>
                <span class="dotted-fill" style="text-align: center;" id="documentAddress"></span>
                <span class="line-label mid">TEL. NO.:</span>
                <span class="dotted-fill short" style="text-align: center;" id="documentTelNo"></span>
              </div>

              <div class="emergency-block">
                <span class="line-label emergency-label">In case of emergency, Please notify:</span>
                <div class="emergency-lines">
                  <div class="line-row">
                    <span class="line-label">Mr. /Mrs.</span>
                    <span class="dotted-fill" style="text-align: center;" id="documentGuardian"></span>
                  </div>
                  <div class="line-row">
                    <span class="line-label">At</span>
                    <span class="dotted-fill" style="text-align: center;" id="documentGuardianAdrress"></span>
                  </div>
                  <div class="line-row">
                    <span class="line-label">Tel No.</span>
                    <span class="dotted-fill" style="text-align: center;" id="documentGuardianTelNo"></span>
                  </div>
                </div>
              </div>
            </section>

            <h3 class="section-heading centered">PHYSICAL EXAMINATION</h3>

            <section class="field-block">
              <div class="line-row four-part">
                <span class="line-label">Blood Pressure:</span>
                <span class="dotted-fill short" style="text-align: center;" id="documentBloodPressure"></span>
                <span class="line-label mid">Temp.:</span>
                <span class="dotted-fill short" style="text-align: center;" id="documentTemp"></span>
                <span class="line-label mid">Pulse Rate:</span>
                <span class="dotted-fill short" style="text-align: center;" id="documentPulseRate"></span>
                <span class="line-label mid">Resp. Rate:</span>
                <span class="dotted-fill short" style="text-align: center;" id="documentRespRate"></span>
              </div>
              <div class="line-row four-part">
                <span class="line-label">Height:</span>
                <span class="dotted-fill short" style="text-align: center;" id="documentHeight"></span>
                <span class="line-label mid">Ft. Weight:</span>
                <span class="dotted-fill short" style="text-align: center;" id="documentWeight"></span>
                <span class="line-label mid">Kg. Ideal Body Weight:</span>
                <span class="dotted-fill" style="text-align: center;" id="documentIdealWeight"></span>
              </div>

              <div class="line-row">
                <span class="line-label">HEAD / NECK :</span>
                <span class="dotted-fill" style="text-align: center;" id="documentHeadNeck"></span>
              </div>
              <div class="line-row">
                <span class="line-label">RESPIRATORY :</span>
                <span class="dotted-fill" style="text-align: center;" id="documentResp"></span>
              </div>
              <div class="line-row">
                <span class="line-label">CARDIO-VASCULAR :</span>
                <span class="dotted-fill" style="text-align: center;" id="documentCardioVascular"></span>
              </div>
              <div class="line-row">
                <span class="line-label">GASTRO-INTESTINAL :</span>
                <span class="dotted-fill" style="text-align: center;" id="documentGastroInternal"></span>
              </div>
              <div class="line-row">
                <span class="line-label">GENITO-URINARY :</span>
                <span class="dotted-fill" style="text-align: center;" id="documentGastroUrinary"></span>
              </div>
              <div class="line-row">
                <span class="line-label">EXTREMITIES :</span>
                <span class="dotted-fill" style="text-align: center;" id="documentExtremities"></span>
              </div>
              <div class="line-row">
                <span class="line-label">NEUROLOGIC :</span>
                <span class="dotted-fill" style="text-align: center;" id="documentNeurologic"></span>
              </div>
            </section>

            <section class="two-column-block">
              <div class="col">
                <p class="col-heading">DIAGNOSIS / TREATMENT / SUGGESTIONS:</p>

                <div class="paragraph-lines" id="diagnosisText"></div>
              </div>

              <div class="col">
                <p class="col-heading">LABORATORY:</p>

                <div class="paragraph-lines" id="laboratoryText"></div>
              </div>
            </section>

            <h3 class="section-heading">PAST MEDICAL / FAMILY HISTORY</h3>

            <section class="checklist-grid">
              <div class="check-col">
                <div class="check-row">
                  <span>Hypertension</span>
                  <span class="paren">(&nbsp;<span class="document-history" id="Hypertension"></span>&nbsp;)</span>
                </div>
                <div class="check-row">
                  <span>Heart Disease</span>
                  <span class="paren">(&nbsp;<span class="document-history" id="Heart Disease"></span>&nbsp;)</span>
                </div>
                <div class="check-row">
                  <span>Asthma</span>
                  <span class="paren">(&nbsp;<span class="document-history" id="Asthma"></span>&nbsp;)</span>
                </div>
                <div class="check-row">
                  <span>Lung Disease</span>
                  <span class="paren">(&nbsp;<span class="document-history" id="Lung Disease"></span>&nbsp;)</span>
                </div>
                <div class="check-row">
                  <span>Malignancy</span>
                  <span class="paren">(&nbsp;<span class="document-history" id="Malignancy"></span>&nbsp;)</span>
                </div>
              </div>
              <div class="check-col">
                <div class="check-row">
                  <span>Allergy</span>
                  <span class="paren">(&nbsp;<span class="document-history" id="Allergy"></span>&nbsp;)</span>
                </div>
                <div class="check-row">
                  <span>Goiter</span>
                  <span class="paren">(&nbsp;<span class="document-history" id="Goiter"></span>&nbsp;)</span>
                </div>
                <div class="check-row">
                  <span>Diabetes</span>
                  <span class="paren">(&nbsp;<span class="document-history" id="Diabetes"></span>&nbsp;)</span>
                </div>
                <div class="check-row">
                  <span>Bleeding Tendency</span>
                  <span class="paren">(&nbsp;<span class="document-history" id="Bleeding Tendency"></span>&nbsp;)</span>
                </div>
                <div class="check-row">
                  <span>Hernia</span>
                  <span class="paren">(&nbsp;<span class="document-history" id="Hernia"></span>&nbsp;)</span>
                </div>
              </div>
              <div class="check-col">
                <div class="check-row">
                  <span>Liver Disease</span>
                  <span class="paren">(&nbsp;<span class="document-history" id="Liver Disease"></span>&nbsp;)</span>
                </div>
                <div class="check-row">
                  <span>Gall Bladder Disease</span>
                  <span class="paren">(&nbsp;<span class="document-history" id="Gall Bladder Disease"></span>&nbsp;)</span>
                </div>
                <div class="check-row">
                  <span>Ulcer</span>
                  <span class="paren">(&nbsp;<span class="document-history" id="Ulcer"></span>&nbsp;)</span>
                </div>
                <div class="check-row">
                  <span>Renal Disease</span>
                  <span class="paren">(&nbsp;<span class="document-history" id="Renal Disease"></span>&nbsp;)</span>
                </div>
                <div class="check-row">
                  <span>Immune Disease</span>
                  <span class="paren">(&nbsp;<span class="document-history" id="Immune Disease"></span>&nbsp;)</span>
                </div>
              </div>
            </section>

            <section class="field-block others-block">
              <p class="others-heading">Others:</p>
              <div class="line-row">
                <span class="line-label">Previous Hospitalization</span>
                <span class="line-label colon">:</span>
                <span class="dotted-fill" id="documentPrevHospitalization"></span>
              </div>
              <div class="line-row">
                <span class="line-label">Previous Operation</span>
                <span class="line-label colon">:</span>
                <span class="dotted-fill" id="documentPrevOperation"></span>
              </div>
              <div class="line-row">
                <span class="line-label">Previous Trauma</span>
                <span class="line-label colon">:</span>
                <span class="dotted-fill" id="documentPrevTrauma"></span>
              </div>

              <div class="history-of-row">
                <span class="line-label history-label">
                  History of<br>
                  <span class="indent">Smoking</span>
                </span>
                  <span class="paren wide">(&nbsp;<span class="document-social-history" id="Smoking"></span>&nbsp;)
                  </span>
                <span class="line-label mid">Alcoholism</span>
                <span class="paren wide">(&nbsp;<span class="document-social-history" id="Alcoholism"></span>&nbsp;)</span>
                <span class="line-label mid">Sports:</span>
                <span class="dotted-fill short" id="documentSportDefinition" style="text-align: center;"></span>
              </div>
            </section>

            <footer class="signature-footer">
              <div class="sig-line"></div>
              <div class="sig-name">Ma. Genevieve L. Cuarto, MD</div>
            </footer>

          </div>
        </div>

        <div class="page-gap"></div>

        <!-- ============ PAGE 2 ============ -->
        <div class="document-page-wrapper">
          <div class="document-page page-two">

            <div class="page2-top">
              <div class="legend-block">
                <p class="legend-title">Legend:</p>
                <table class="legend-table">
                  <tr>
                    <td>C</td>
                    <td>-</td>
                    <td>Dental Caries</td>
                  </tr>

                  <tr>
                    <td>C1</td>
                    <td>-</td>
                    <td>Dental Caries with Vital<br>Pulp Exposed</td>
                  </tr>

                  <tr>
                    <td>C2</td>
                    <td>-</td>
                    <td>Dental Caries with<br>Non-Vital Pulp Exposed</td>
                  </tr>

                  <tr>
                    <td>X</td>
                    <td>-</td>
                    <td>Indicated for Extraction</td>
                  </tr>

                  <tr>
                    <td>RF</td>
                    <td>-</td>
                    <td>Retained Root Fragment</td>
                  </tr>

                  <tr>
                    <td>AM</td>
                    <td>-</td>
                    <td>Amalgam Filling</td>
                  </tr>

                  <tr>
                    <td>S</td>
                    <td>-</td>
                    <td>Silicate Filling</td>
                  </tr>
                  
                  <tr>
                    <td>GC</td>
                    <td>-</td>
                    <td>Gold Crown</td>
                  </tr>

                  <tr>
                    <td>AB</td>
                    <td>-</td>
                    <td>Bridge Abutment</td>
                  </tr>

                  <tr>
                    <td>P</td>
                    <td>-</td>
                    <td>Pontic</td>
                  </tr>

                  <tr>
                    <td>U</td>
                    <td>-</td>
                    <td>Gold Clasp</td>
                  </tr>

                  <tr>
                    <td>GI</td>
                    <td>-</td>
                    <td>Gold Inlay</td>
                  </tr>

                  <tr>
                    <td>M</td>
                    <td>-</td>
                    <td>Missing due to Extraction</td>
                  </tr>

                  <tr>
                    <td>Un</td>
                    <td>-</td>
                    <td>Unerupted</td>
                  </tr>
                </table>
              </div>

              <div class="dental-chart-block">
                <h2 class="dental-title">DENTAL RECORD</h2>

                <div class="dental-block">
                  <div class="side-labels">
                    <span class="side-label right">
                      LEFT<br>UPPER
                    </span>
                    <span class="side-label right">
                      LEFT<br>LOWER
                    </span>
                  </div>

                  <div class="teeth-block">
                    <div class="teeth-block-upper">
                      <div class="tooth-field-row">
                        <div class="teeth-block-content" id="t1"></div>
                        <div class="teeth-block-content" id="t2"></div>
                        <div class="teeth-block-content" id="t3"></div>
                        <div class="teeth-block-content" id="t4"></div>
                        <div class="teeth-block-content" id="t5"></div>
                        <div class="teeth-block-content" id="t6"></div>
                        <div class="teeth-block-content" id="t7"></div>
                        <div class="teeth-block-content" id="t8"></div>
                        <div class="teeth-block-content" id="t9"></div>
                        <div class="teeth-block-content" id="t10"></div>
                        <div class="teeth-block-content" id="t11"></div>
                        <div class="teeth-block-content" id="t12"></div>
                        <div class="teeth-block-content" id="t13"></div>
                        <div class="teeth-block-content" id="t14"></div>
                        <div class="teeth-block-content" id="t15"></div>
                        <div class="teeth-block-content" id="t16"></div>
                      </div>
                      <div class="tooth-row upper-row">
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                      </div>
                    </div>

                    <div class="teeth-block-lower">
                      <div class="tooth-field-row">
                        <div class="teeth-block-content" id="t32"></div>
                        <div class="teeth-block-content" id="t31"></div>
                        <div class="teeth-block-content" id="t30"></div>
                        <div class="teeth-block-content" id="t29"></div>
                        <div class="teeth-block-content" id="t28"></div>
                        <div class="teeth-block-content" id="t27"></div>
                        <div class="teeth-block-content" id="t26"></div>
                        <div class="teeth-block-content" id="t25"></div>
                        <div class="teeth-block-content" id="t24"></div>
                        <div class="teeth-block-content" id="t23"></div>
                        <div class="teeth-block-content" id="t22"></div>
                        <div class="teeth-block-content" id="t21"></div>
                        <div class="teeth-block-content" id="t20"></div>
                        <div class="teeth-block-content" id="t19"></div>
                        <div class="teeth-block-content" id="t18"></div>
                        <div class="teeth-block-content" id="t17"></div>
                      </div>
                      <div class="tooth-row lower-row">
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                        <span class="tooth">
                          <img
                              src="<?= BASE_URL ?>assets/svg/tooth.svg"
                              alt="Tooth"
                              height=18
                              width=18
                          >
                        </span>
                      </div>
                    </div>

                  </div>

                  <div class="side-labels lower-side">
                    <span class="side-label left">
                      RIGHT<br>UPPER
                    </span>
                    <span class="side-label left">
                      RIGHT<br>LOWER
                    </span>
                  </div>
                </div>

              </div>
            </div>

            <h3 class="section-heading centered">TREATMENT PLAN</h3>

            <table class="treatment-plan-table">
              <tr>
                <td class="tp-label">Oral Surgery Clinic</td>
                <td class="document-treatment-plan" id="oral_surgery"></td>
              </tr>

              <tr>
                <td class="tp-label">Operative Clinic</td>
                <td class="document-treatment-plan" id="operative"></td>
              </tr>

              <tr>
                <td class="tp-label">Prosthodontia Clinic</td>
                <td class="document-treatment-plan" id="prosthodontia"></td>
              </tr>
              
              <tr>
                <td class="tp-label">Crown &amp; Bridge Clinic</td>
                <td class="document-treatment-plan" id="crown_bridge"></td>
              </tr>

              <tr>
                <td class="tp-label">Oral Medicine Clinic</td>
                <td class="document-treatment-plan" id="oral_medicine"></td>
              </tr>

              <tr>
                <td class="tp-label">X-Ray Clinic</td>
                <td class="document-treatment-plan" id="x_ray"></td>
              </tr>

              <tr>
                <td class="tp-label">Children's Clinic</td>
                <td class="document-treatment-plan" id="children"></td>
              </tr>

              <tr>
                <td class="tp-label">Orthodontia Clinic</td>
                <td class="document-treatment-plan" id="orthodontia"></td>
              </tr>

              <tr>
                <td class="tp-label">Clinical Laboratory</td>
                <td class="document-treatment-plan" id="clinical_lab"></td>
              </tr>
            </table>

            <table class="services-table">
              <thead>
                <tr>
                  <th class="col-date">DATE</th>
                  <th class="col-services">SERVICES RENDERED</th>
                  <th class="col-sig">Patient's Signature</th>
                  <th class="col-sig">Dentist's Signature</th>
                </tr>
              </thead>
              <tbody class="document-services-body">
                <tr data-service-row="0">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="1">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="2">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="3">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="4">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="5">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="6">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="7">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="8">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="9">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="10">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="11">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="12">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="13">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="14">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="15">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="16">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="17">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="18">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>

                <tr data-service-row="19">
                  <td class="service-date"></td>
                  <td class="service-rendered"></td>
                  <td class="service-patient-signature"></td>
                  <td class="service-dentist-signature"></td>
                </tr>


              </tbody>
            </table>

            <footer class="page2-footer">
              <p class="examined-by">Examined by:</p>
              <div class="sig-line dentist-sig"></div>
              <div class="sig-name">Edwin D. Elma, D.M.D.</div>
              <div class="sig-role">Dentist</div>

              <div class="footer-bottom-row">
                <span class="form-code">AFA-UHS-1.01.F4</span>
                <span class="page-number">Page 2 of 2</span>
              </div>
            </footer>

          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
  window.BASE_URL = <?= json_encode(BASE_URL) ?>;
  window.API_URL = <?= json_encode(API_URL) ?>;
</script>

<script src="<?= BASE_URL ?>assets/js/components/modal.js"></script>
<script src="<?= BASE_URL ?>assets/js/patients/index.js" type="module"></script>
</body>
</html>
