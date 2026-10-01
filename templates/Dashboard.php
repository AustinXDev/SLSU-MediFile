<?php

require_once __DIR__ . '/../config/init.php';

use App\Middleware\AdminMiddleware;
use App\Session\SessionManager;

$session = new SessionManager();
$middleware = new AdminMiddleware($session);

$middleware->requireAuth();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SLSU-MEDIFILE | Administrator Dashboard</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

</head>

<body>

<div class="dashboard">

    <!-- ==========================================
         SIDEBAR
    =========================================== -->

    <aside class="sidebar" id="sidebar">

        <div class="brand">

            <div class="brand-logo">
                <img width="50" src="<?= BASE_URL ?>assets/images/SLSU-LOGO.png" alt="SLSU-LOGO">
            </div>

            <div class="brand-text">
                <h1>SLSU-Health Record</h1>
                <p>Medical Records System</p>
            </div>

        </div>


        <!-- Administrator -->
        <div class="admin-profile">

            <div class="admin-avatar">
                AD
            </div>

            <div class="admin-info">
                <strong>
                    <?= htmlspecialchars($session->get('admin_username') ?? '') ?>
                </strong>

                <span>
                    <?= htmlspecialchars($session->get('role') ?? '') ?>
                </span>

                <small>
                    <i></i>
                    Online
                </small>
            </div>

        </div>


        <!-- Navigation -->
        <nav class="navigation">

            <p class="nav-label">MAIN MENU</p>

            <a href="#" class="nav-item active">
                <span class="nav-icon"><i class="fa-solid fa-house"></i></span>
                <span>Dashboard</span>
            </a>

            <a href="<?= BASE_URL ?>Patients" class="nav-item">
                <span class="nav-icon"><i class="fa-solid fa-user-injured"></i></span>
                <span>Patient Records</span>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon"><i class="fa-solid fa-user-nurse"></i></span>
                <span>Users / Staff</span>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span>
                <span>Reports & Analytics</span>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                <span>Activity Logs</span>
            </a>

            <a href="#" class="nav-item notification-nav">
                <span class="nav-icon"><i class="fa-solid fa-bell"></i></span>
                <span>Notifications</span>
                <b>3</b>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon"><i class="fa-solid fa-gear"></i></span>
                <span>Settings</span>
            </a>

        </nav>


        <!-- Logout -->
        <div class="sidebar-bottom">

            <a href="#" class="logout">
                <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                <span>Logout</span>
            </a>

        </div>

    </aside>


    <!-- MOBILE OVERLAY -->
    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
        onclick="closeSidebar()"
    ></div>


    <!-- ==========================================
         MAIN CONTENT
    =========================================== -->

    <main class="main">

        <!-- HEADER -->
        <header class="top-header">

            <div class="header-left">

                <button
                    class="mobile-menu"
                    onclick="openSidebar()"
                >
                    <i class="fa-solid fa-bars"></i>
                </button>

                <div>
                    <h2>Dashboard</h2>

                    <p>
                        Welcome back,
                        <strong>Administrator</strong>
                    </p>
                </div>

            </div>


            <div class="header-right">

                <!-- Search -->
                <div class="search">

                    <span><i class="fa-solid fa-magnifying-glass"></i></span>

                    <input
                        type="text"
                        placeholder="Search records, patients..."
                    >

                </div>


                <!-- Notification -->
                <button class="header-button notification-button">
                    <i class="fa-solid fa-bell"></i>
                    <i></i>
                </button>


                <!-- Profile -->
                <button class="header-profile">

                    <div class="profile-avatar">
                        AD
                    </div>

                    <div class="profile-details">

                        <strong>
                            <?= htmlspecialchars($session->get('admin_username') ?? '') ?>
                        </strong>

                        <span>
                            <?= htmlspecialchars($session->get('role') ?? '') ?>
                        </span>

                    </div>

                    <span class="profile-arrow"><i class="fa-solid fa-chevron-down"></i></span>

                </button>

            </div>

        </header>


        <!-- CONTENT -->
        <div class="content">

            <!-- ======================================
                 STATISTICS
            ======================================= -->

            <section class="statistics">

                <!-- Patients -->
                <article class="stat-card">

                    <div class="stat-top">

                        <div class="stat-icon green">
                            <i class="fa-solid fa-user-injured"></i>
                        </div>

                        <button>•••</button>

                    </div>

                    <p>Total Patients</p>

                    <h3 id="totalPatient">0</h3>

                    <div id="totalPatientChange" class="stat-change">
                        <span class="stat-change-value"></span>
                        <span class="stat-change-label">vs last month</span>
                    </div>

                </article>
                
                <!-- Users -->
                <article class="stat-card">

                    <div class="stat-top">

                        <div class="stat-icon green">
                            <i class="fa-solid fa-user-doctor"></i>
                        </div>

                        <button>•••</button>

                    </div>

                    <p>Total Staffs</p>

                    <h3 id="totalStaffs">0</h3>

                    <div id="totalStaffChange" class="stat-change">
                        <span class="stat-change-value"></span>
                        <span class="stat-change-label">vs last month</span>
                    </div>

                </article>


                <!-- Records -->
                <article class="stat-card">

                    <div class="stat-top">

                        <div class="stat-icon mint">
                            <i class="fa-solid fa-file-medical"></i>
                        </div>

                        <button>•••</button>

                    </div>

                    <p>Active Medical Records</p>

                    <h3 id="totalActiveRecords">0</h3>

                    <div id="totalActiveChange" class="stat-change">
                        <span class="stat-change-value"></span>
                        <span class="stat-change-label">vs last month</span>
                    </div>

                </article>


                <!-- Appointments -->
                <article class="stat-card">

                    <div class="stat-top">

                        <div class="stat-icon teal">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>

                        <button>•••</button>

                    </div>

                    <p>Today's Services</p>

                    <h3 id="totalServices">0</h3>

                    <div id="totalServiceChange" class="stat-change">
                        <span class="stat-change-value"></span>
                        <span class="stat-change-label">vs yesterday</span>
                    </div>

                </article>


                <!-- Dental -->
                <article class="stat-card">

                    <div class="stat-top">

                        <div class="stat-icon emerald">
                            <i class="fa-solid fa-tooth"></i>
                        </div>

                        <button>•••</button>

                    </div>

                    <p>Dental Services</p>

                    <h3 id="totalDentalServices">0</h3>

                    <div id="totalDentalChange" class="stat-change">
                        <span class="stat-change-value"></span>
                        <span class="stat-change-label">vs last month</span>
                    </div>

                </article>

            </section>


            <!-- ======================================
                 ANALYTICS
            ======================================= -->

            <section class="analytics-grid">

                <!-- Chart -->
                <article class="card chart-card">

                    <div class="card-header">

                        <div class="card-title">

                            <div class="title-icon">
                                <i class="fa-solid fa-arrow-trend-up"></i>
                            </div>

                            <div>
                                <h3>Patient Records Activity</h3>
                                <p>Patient record activity over time</p>
                            </div>

                        </div>


                        <div class="chart-filter">

                            <button
                                type="button"
                                class="active"
                                data-period="weekly"
                            >
                                Weekly
                            </button>

                            <button
                                type="button"
                                data-period="monthly"
                            >
                                Monthly
                            </button>

                            <button
                                type="button"
                                data-period="yearly"
                            >
                                Yearly
                            </button>

                        </div>

                    </div>


                    <div class="chart">

                        <div id="chartYAxis" class="chart-y-axis">
                            <span>1,000</span>
                            <span>800</span>
                            <span>600</span>
                            <span>400</span>
                            <span>200</span>
                            <span>0</span>
                        </div>


                        <div class="chart-area">

                            <div class="grid-line"></div>
                            <div class="grid-line"></div>
                            <div class="grid-line"></div>
                            <div class="grid-line"></div>
                            <div class="grid-line"></div>


                            <svg
                                id="patientActivityChart"
                                class="chart-svg"
                                viewBox="0 0 700 260"
                                preserveAspectRatio="none"
                            >

                                <defs>

                                    <linearGradient
                                        id="chartGradient"
                                        x1="0"
                                        y1="0"
                                        x2="0"
                                        y2="1"
                                    >

                                        <stop
                                            offset="0%"
                                            stop-color="#10b981"
                                            stop-opacity=".22"
                                        />

                                        <stop
                                            offset="100%"
                                            stop-color="#10b981"
                                            stop-opacity="0"
                                        />

                                    </linearGradient>

                                </defs>


                                <path class="chart-fill"></path>

                                <path class="chart-line"></path>

                            </svg>


                            <div id="chartDays" class="chart-days">
                            </div>

                        </div>

                    </div>


                    <div class="chart-legend">

                        <span>
                            <i></i>
                            Patient Records
                        </span>

                        <span>
                            <strong id="activityTotal">0</strong>
                            <span id="activityLabel">total this week</span>
                        </span>

                    </div>

                </article>


                <!-- Records Overview -->
                <article class="card records-card">

                    <div class="card-header">

                        <div class="card-title">

                            <div class="title-icon">
                                <i class="fa-solid fa-file-medical"></i>
                            </div>

                            <div>
                                <h3>Medical Records Overview</h3>
                                <p>Current medical record status</p>
                            </div>

                        </div>

                    </div>


                    <div class="record-list">

                        <!-- New Records -->
                        <div class="record-item">

                            <div class="record-info">
                                <span>New This Month</span>
                                <strong id="newRecords">0</strong>
                            </div>

                            <div class="progress">
                                <span id="newRecordsProgress"></span>
                            </div>

                            <small id="newRecordsPercent">0%</small>

                        </div>


                        <!-- Updated Records -->
                        <div class="record-item">

                            <div class="record-info">
                                <span>Updated This Month</span>
                                <strong id="updatedRecords">0</strong>
                            </div>

                            <div class="progress">
                                <span id="updatedRecordsProgress"></span>
                            </div>

                            <small id="updatedRecordsPercent">0%</small>

                        </div>


                        <!-- Complete Records -->
                        <div class="record-item">

                            <div class="record-info">
                                <span>Medical Examinations</span>
                                <strong id="medicalRecords">0</strong>
                            </div>

                            <div class="progress">
                                <span id="medicalRecordsProgress"></span>
                            </div>

                            <small id="medicalRecordsPercent">0%</small>

                        </div>


                        <!-- Incomplete Records -->
                        <div class="record-item">

                            <div class="record-info">
                                <span>Dental Records</span>
                                <strong id="dentalRecords">0</strong>
                            </div>

                            <div class="progress pending">
                                <span id="dentalRecordsProgress"></span>
                            </div>

                            <small id="incompleteRecordsPercent">0%</small>

                        </div>

                    </div>


                    <div class="record-total">

                        <span>Total Records</span>

                        <strong id="totalMedicalRecords">0</strong>

                    </div>

                </article>

            </section>


            <!-- ======================================
                 LOWER CONTENT
            ======================================= -->

            <section class="bottom-grid">

                <!-- Recent Activity -->
                <article class="card table-card">

                    <div class="card-header">

                        <div class="card-title">

                            <div class="title-icon">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>

                            <div>
                                <h3>Recent Activity</h3>
                                <p>Latest administrator and staff activity</p>
                            </div>

                        </div>

                        <button class="view-button">
                            View All
                        </button>

                    </div>


                    <div class="table-wrapper">

                        <table>

                            <thead>
                                <tr>
                                    <th>STAFF</th>
                                    <th>ACTIVITY</th>
                                    <th>DATE & TIME</th>
                                    <th>STATUS</th>
                                </tr>
                            </thead>

                            <tbody id="recentActivityBody">
                            </tbody>

                        </table>

                    </div>

                </article>


                <!-- Recent Patient Records -->
                <article class="card table-card">

                    <div class="card-header">

                        <div class="card-title">

                            <div class="title-icon">
                                <i class="fa-solid fa-user-plus"></i>
                            </div>

                            <div>
                                <h3>Recent Patient Records</h3>
                                <p>Latest patients added to the system</p>
                            </div>

                        </div>

                        <button
                            type="button"
                            class="view-button"
                            id="viewPatients"
                        >
                            View All
                        </button>

                    </div>


                    <div class="recent-table-wrapper">

                        <table>

                            <thead>
                                <tr>
                                    <th>PATIENT</th>
                                    <th>DEPARTMENT</th>
                                    <th>DATE ADDED</th>
                                </tr>
                            </thead>

                            <tbody id="recentPatientsBody">
                                <!-- Patient records rendered by JavaScript -->
                            </tbody>

                        </table>

                    </div>

                </article>

            </section>

        </div>

    </main>

</div>


<script>

function openSidebar() {

    document
        .getElementById("sidebar")
        .classList.add("sidebar-open");

    document
        .getElementById("sidebarOverlay")
        .classList.add("active");

}


function closeSidebar() {

    document
        .getElementById("sidebar")
        .classList.remove("sidebar-open");

    document
        .getElementById("sidebarOverlay")
        .classList.remove("active");

}


// Animate progress bars from 0 up to their target width on load
document.addEventListener("DOMContentLoaded", function () {

    var bars = document.querySelectorAll(".progress span");

    bars.forEach(function (bar) {
        var target = bar.style.width;
        bar.style.width = "0%";

        requestAnimationFrame(function () {
            setTimeout(function () {
                bar.style.width = target;
            }, 150);
        });
    });

});

</script>

<script src="<?= BASE_URL ?>assets/js/dashboard/index.js" type="module"></script>

</body>
</html>