<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SLSU-MEDIFILE | Administrator Dashboard</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

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
                <strong>Administrator</strong>
                <span>System Administrator</span>

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
                <span class="nav-icon"><i class="fa-solid fa-file-medical"></i></span>
                <span>Medical Records</span>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon"><i class="fa-solid fa-calendar-check"></i></span>
                <span>Appointments</span>
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

        <div 
          style="
            position: relative; 
            height: 150px; 
            overflow: hidden;
          ">

          <img 
            height="100%" 
            width="100%" 
            style="
              filter:contrast(80%); 
              opacity: 0.1;
              object-fit: cover;
              transform: scale(1.1);" 
            src="<?= BASE_URL ?>assets/images/SLSU-SCHOOL.png" 
            alt="SLSU-SCHOOL"
          >

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
                        <strong>Administrator</strong>
                        <span>Admin</span>
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

                    <h3>12,480</h3>

                    <div class="stat-change positive">
                        ↑ 8.2%
                        <span>vs last month</span>
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

                    <h3>10,284</h3>

                    <div class="stat-change positive">
                        ↑ 5.4%
                        <span>vs last month</span>
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

                    <p>Today's Appointments</p>

                    <h3>24</h3>

                    <div class="stat-change positive">
                        ↑ 12.1%
                        <span>vs yesterday</span>
                    </div>

                </article>


                <!-- Staff -->
                <article class="stat-card">

                    <div class="stat-top">

                        <div class="stat-icon emerald">
                            <i class="fa-solid fa-user-nurse"></i>
                        </div>

                        <button>•••</button>

                    </div>

                    <p>Registered Staff</p>

                    <h3>186</h3>

                    <div class="stat-change positive">
                        ↑ 3.8%
                        <span>vs last month</span>
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

                            <button class="active">
                                Weekly
                            </button>

                            <button>
                                Monthly
                            </button>

                            <button>
                                Yearly
                            </button>

                        </div>

                    </div>


                    <div class="chart">

                        <div class="chart-y-axis">
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


                                <path
                                    class="chart-fill"
                                    d="
                                        M0,190
                                        C45,170 70,185 105,145
                                        C140,105 160,135 205,110
                                        C245,85 265,105 305,65
                                        C350,25 370,70 415,52
                                        C460,35 480,80 520,65
                                        C560,45 590,80 625,40
                                        C660,15 680,35 700,20
                                        L700,260
                                        L0,260
                                        Z
                                    "
                                />

                                <path
                                    class="chart-line"
                                    d="
                                        M0,190
                                        C45,170 70,185 105,145
                                        C140,105 160,135 205,110
                                        C245,85 265,105 305,65
                                        C350,25 370,70 415,52
                                        C460,35 480,80 520,65
                                        C560,45 590,80 625,40
                                        C660,15 680,35 700,20
                                    "
                                />

                            </svg>


                            <div class="chart-days">
                                <span>Mon</span>
                                <span>Tue</span>
                                <span>Wed</span>
                                <span>Thu</span>
                                <span>Fri</span>
                                <span>Sat</span>
                                <span>Sun</span>
                            </div>

                        </div>

                    </div>


                    <div class="chart-legend">

                        <span>
                            <i></i>
                            Patient Records
                        </span>

                        <span>
                            <strong>7,420</strong>
                            total this week
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
                                <p>Current record distribution</p>
                            </div>

                        </div>

                    </div>


                    <div class="record-list">

                        <div class="record-item">

                            <div class="record-info">
                                <span>New Records</span>
                                <strong>1,254</strong>
                            </div>

                            <div class="progress">
                                <span
                                    style="width: 28%"
                                ></span>
                            </div>

                            <small>28%</small>

                        </div>


                        <div class="record-item">

                            <div class="record-info">
                                <span>Updated Records</span>
                                <strong>3,482</strong>
                            </div>

                            <div class="progress">
                                <span
                                    style="width: 39%"
                                ></span>
                            </div>

                            <small>39%</small>

                        </div>


                        <div class="record-item">

                            <div class="record-info">
                                <span>Pending Records</span>
                                <strong>1,026</strong>
                            </div>

                            <div class="progress pending">
                                <span
                                    style="width: 15%"
                                ></span>
                            </div>

                            <small>15%</small>

                        </div>


                        <div class="record-item">

                            <div class="record-info">
                                <span>Archived Records</span>
                                <strong>4,522</strong>
                            </div>

                            <div class="progress archived">
                                <span
                                    style="width: 18%"
                                ></span>
                            </div>

                            <small>18%</small>

                        </div>

                    </div>


                    <div class="record-total">

                        <span>Total Records</span>

                        <strong>10,284</strong>

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


                            <tbody>

                                <tr>

                                    <td>
                                        <div class="user-cell">

                                            <div class="user-avatar pink">
                                                MS
                                            </div>

                                            <span>
                                                Dr. Maria Santos
                                            </span>

                                        </div>
                                    </td>

                                    <td>
                                        Updated patient medical record
                                    </td>

                                    <td>
                                        May 22, 2025 · 10:24 AM
                                    </td>

                                    <td>
                                        <span class="status completed">
                                            Completed
                                        </span>
                                    </td>

                                </tr>


                                <tr>

                                    <td>
                                        <div class="user-cell">

                                            <div class="user-avatar blue">
                                                JD
                                            </div>

                                            <span>
                                                Juan Dela Cruz
                                            </span>

                                        </div>
                                    </td>

                                    <td>
                                        Added new patient
                                    </td>

                                    <td>
                                        May 22, 2025 · 09:15 AM
                                    </td>

                                    <td>
                                        <span class="status completed">
                                            Completed
                                        </span>
                                    </td>

                                </tr>


                                <tr>

                                    <td>
                                        <div class="user-cell">

                                            <div class="user-avatar purple">
                                                AR
                                            </div>

                                            <span>
                                                Angela Reyes
                                            </span>

                                        </div>
                                    </td>

                                    <td>
                                        Generated medical report
                                    </td>

                                    <td>
                                        May 22, 2025 · 08:40 AM
                                    </td>

                                    <td>
                                        <span class="status completed">
                                            Completed
                                        </span>
                                    </td>

                                </tr>


                                <tr>

                                    <td>
                                        <div class="user-cell">

                                            <div class="user-avatar orange">
                                                AS
                                            </div>

                                            <span>
                                                Admin Support
                                            </span>

                                        </div>
                                    </td>

                                    <td>
                                        Updated staff information
                                    </td>

                                    <td>
                                        May 21, 2025 · 04:35 PM
                                    </td>

                                    <td>
                                        <span class="status completed">
                                            Completed
                                        </span>
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </article>


                <!-- Appointments -->
                <article class="card table-card">

                    <div class="card-header">

                        <div class="card-title">

                            <div class="title-icon">
                                <i class="fa-solid fa-calendar-check"></i>
                            </div>

                            <div>
                                <h3>Upcoming Appointments</h3>
                                <p>Today's scheduled appointments</p>
                            </div>

                        </div>

                        <button class="view-button">
                            View All
                        </button>

                    </div>


                    <div class="appointment-list">

                        <div class="appointment">

                            <div class="appointment-time">
                                <strong>09:30</strong>
                                <span>AM</span>
                            </div>

                            <div class="appointment-details">

                                <strong>Anna Reyes</strong>

                                <span>
                                    Dr. Maria Santos
                                </span>

                            </div>

                            <span class="status completed">
                                Confirmed
                            </span>

                        </div>


                        <div class="appointment">

                            <div class="appointment-time">
                                <strong>10:15</strong>
                                <span>AM</span>
                            </div>

                            <div class="appointment-details">

                                <strong>John Dela Cruz</strong>

                                <span>
                                    Dr. James Wilson
                                </span>

                            </div>

                            <span class="status completed">
                                Confirmed
                            </span>

                        </div>


                        <div class="appointment">

                            <div class="appointment-time">
                                <strong>01:00</strong>
                                <span>PM</span>
                            </div>

                            <div class="appointment-details">

                                <strong>Linda Garcia</strong>

                                <span>
                                    Dr. Maria Santos
                                </span>

                            </div>

                            <span class="status pending-status">
                                Pending
                            </span>

                        </div>


                        <div class="appointment">

                            <div class="appointment-time">
                                <strong>02:30</strong>
                                <span>PM</span>
                            </div>

                            <div class="appointment-details">

                                <strong>Michael Johnson</strong>

                                <span>
                                    Dr. James Wilson
                                </span>

                            </div>

                            <span class="status completed">
                                Confirmed
                            </span>

                        </div>

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

</body>
</html>