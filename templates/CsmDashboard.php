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
    <title>SLSU-MEDIFILE | Client Satisfaction Measurement</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/patients.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/csm-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
<div class="dashboard">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-logo"><img width="50" src="<?= BASE_URL ?>assets/images/SLSU-LOGO.png" alt="SLSU logo"></div>
            <div class="brand-text"><h1>SLSU-Health Record</h1><p>Medical Records System</p></div>
        </div>
        <div class="admin-profile">
            <div class="admin-avatar">AD</div>
            <div class="admin-info">
                <strong><?= htmlspecialchars($session->get('admin_username') ?? '') ?></strong>
                <span><?= htmlspecialchars($session->get('role') ?? '') ?></span>
                <small><i></i>Online</small>
            </div>
        </div>
        <nav class="navigation">
            <p class="nav-label">MAIN MENU</p>
            <a href="<?= BASE_URL ?>Dashboard" class="nav-item">
                <span class="nav-icon"><i class="fa-solid fa-house"></i></span><span>Dashboard</span>
            </a>
            <a href="patients" class="nav-item">
                <span class="nav-icon"><i class="fa-solid fa-user-injured"></i></span><span>Patient Records</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon"><i class="fa-solid fa-user-nurse"></i></span><span>Users / Staff</span>
            </a>
            <a href="CsmDashboard" class="nav-item active" aria-current="page">
                <span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span><span>Reports &amp; Analytics</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></span><span>Activity Logs</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon"><i class="fa-solid fa-gear"></i></span><span>Settings</span>
            </a>
        </nav>
        <div class="sidebar-bottom">
            <a href="#" class="logout">
                <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span><span>Logout</span>
            </a>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <main class="main">
        <header class="top-header">
            <div class="header-left">
                <button class="mobile-menu" onclick="openSidebar()" aria-label="Open navigation"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <h2>Client Satisfaction Measurement</h2>
                    <p>Welcome back, <strong>Administrator</strong></p>
                </div>
            </div>
            <div class="header-right">
                <button class="header-button notification-button" aria-label="Notifications"><i class="fa-solid fa-bell"></i><i></i></button>
                <button class="header-profile" aria-label="Administrator profile">
                    <div class="profile-avatar">AD</div>
                    <div class="profile-details">
                        <strong><?= htmlspecialchars($session->get('admin_username') ?? '') ?></strong>
                        <span><?= htmlspecialchars($session->get('role') ?? '') ?></span>
                    </div>
                    <span class="profile-arrow"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
            </div>
        </header>

        <div class="content csm-content">
            <section class="page-heading csm-page-heading">
                <div>
                    <span class="eyebrow-label"><i class="fa-solid fa-chart-line"></i> Client feedback</span>
                    <h1>Client Satisfaction Measurement</h1>
                    <p>Monitor, analyze, and review client satisfaction results.</p>
                </div>
            </section>

            <form class="card patient-toolbar csm-filters" id="csmFilters" aria-label="Filter CSM results">
                <label class="filter-field csm-date-range">
                    <span>Date Range</span>
                    <span class="csm-date-inputs">
                        <input id="dateFrom" name="from" type="date" aria-label="Start date">
                        <span aria-hidden="true">to</span>
                        <input id="dateTo" name="to" type="date" aria-label="End date">
                    </span>
                </label>
                <label class="filter-field">
                    <span>Region</span>
                    <select id="regionFilter" name="region"><option value="">All Regions</option></select>
                </label>
                <label class="filter-field">
                    <span>Client Type</span>
                    <select id="clientTypeFilter" name="clientType"><option value="">All Clients</option></select>
                </label>
                <div class="csm-filter-actions">
                    <button class="primary-button" type="submit"><i class="fa-solid fa-filter"></i> Apply Filters</button>
                    <button class="clear-filter" id="resetFilters" type="button">Reset</button>
                </div>
            </form>

            <p class="csm-status" id="csmStatus" role="status" aria-live="polite"></p>

            <section class="statistics csm-summary" aria-label="CSM summary">
                <article class="stat-card">
                    <div class="stat-top"><div class="stat-icon green"><i class="fa-solid fa-clipboard-check"></i></div></div>
                    <p>Total CSM Responses</p><h3 id="totalResponses">--</h3>
                    <div class="stat-change"><span class="stat-change-label">Matching selected filters</span></div>
                </article>
                <article class="stat-card">
                    <div class="stat-top"><div class="stat-icon mint"><i class="fa-solid fa-star"></i></div></div>
                    <p>Overall Satisfaction</p><h3 id="overallSatisfaction">--</h3>
                    <div class="stat-change"><span class="stat-change-label">Average across applicable SQD responses</span></div>
                </article>
                <article class="stat-card">
                    <div class="stat-top"><div class="stat-icon teal"><i class="fa-solid fa-face-smile"></i></div></div>
                    <p>Satisfaction Rate</p><h3 id="satisfactionRate">--</h3>
                    <div class="stat-change"><span class="stat-change-label">Agree or Strongly Agree</span></div>
                </article>
                <article class="stat-card">
                    <div class="stat-top"><div class="stat-icon emerald"><i class="fa-solid fa-calendar-day"></i></div></div>
                    <p>Responses Today</p><h3 id="responsesToday">--</h3>
                    <div class="stat-change"><span class="stat-change-label">For the current selected filters</span></div>
                </article>
            </section>

            <section class="card csm-section csm-dimensions-section">
                <div class="card-header">
                    <div class="card-title"><div class="title-icon"><i class="fa-solid fa-list-check"></i></div><div><h3>Satisfaction Dimensions</h3><p>Average rating and applicable responses for each service quality dimension</p></div></div>
                </div>
                <div class="csm-dimensions" id="sqdDimensions"></div>
            </section>

            <div class="csm-grid csm-grid-equal">
                <section class="card csm-section">
                    <div class="card-header">
                        <div class="card-title"><div class="title-icon"><i class="fa-solid fa-chart-pie"></i></div><div><h3>Rating Distribution</h3><p>All SQD responses, including Not Applicable</p></div></div>
                    </div>
                    <div class="csm-bar-list" id="ratingDistribution"></div>
                </section>
                <section class="card csm-section">
                    <div class="card-header">
                        <div class="card-title"><div class="title-icon"><i class="fa-solid fa-chart-column"></i></div><div><h3>Satisfaction Trend</h3><p>Average rating over time</p></div></div>
                        <div class="chart-filter" role="group" aria-label="Trend period">
                            <button type="button" data-trend="daily" aria-pressed="true">Daily</button>
                            <button type="button" data-trend="weekly" aria-pressed="false">Weekly</button>
                            <button type="button" data-trend="monthly" aria-pressed="false">Monthly</button>
                        </div>
                    </div>
                    <div class="csm-trend-chart" id="satisfactionTrend" aria-label="Average satisfaction trend"></div>
                </section>
            </div>

            <section class="card csm-section">
                <div class="card-header">
                    <div class="card-title"><div class="title-icon"><i class="fa-solid fa-landmark"></i></div><div><h3>Citizen's Charter Results</h3><p>Actual stored response categories and their share of answered responses</p></div></div>
                </div>
                <div class="csm-charter-grid">
                    <section class="csm-charter-dimension" data-charter="CC1"><h4>CC1 - Awareness</h4><div class="csm-bar-list"></div></section>
                    <section class="csm-charter-dimension" data-charter="CC2"><h4>CC2 - Visibility</h4><div class="csm-bar-list"></div></section>
                    <section class="csm-charter-dimension" data-charter="CC3"><h4>CC3 - Helpfulness</h4><div class="csm-bar-list"></div></section>
                </div>
            </section>

            <section class="card csm-section">
                <div class="card-header">
                    <div class="card-title"><div class="title-icon"><i class="fa-solid fa-hand-holding-medical"></i></div><div><h3>Service Results</h3><p>Responses and average satisfaction by service availed</p></div></div>
                </div>
                <div class="table-wrapper csm-table-wrap">
                    <table class="csm-table">
                        <thead><tr><th>Service Name</th><th>Number of Responses</th><th>Average Satisfaction</th></tr></thead>
                        <tbody id="serviceResults"></tbody>
                    </table>
                </div>
            </section>

            <section class="card csm-section">
                <div class="card-header">
                    <div class="card-title"><div class="title-icon"><i class="fa-solid fa-users"></i></div><div><h3>Client Demographics</h3><p>Distribution of respondents matching selected filters</p></div></div>
                </div>
                <div class="csm-demographics-grid">
                    <section><h4>Client Type</h4><div class="csm-bar-list" id="demographicClientType"></div></section>
                    <section><h4>Sex</h4><div class="csm-bar-list" id="demographicSex"></div></section>
                    <section><h4>Age Group</h4><div class="csm-bar-list" id="demographicAge"></div></section>
                    <section><h4>Region</h4><div class="csm-bar-list" id="demographicRegion"></div></section>
                </div>
            </section>

            <section class="card csm-section">
                <div class="card-header">
                    <div class="card-title"><div class="title-icon"><i class="fa-solid fa-comment-dots"></i></div><div><h3>Client Suggestions</h3><p>Recent client comments</p></div></div>
                </div>
                <div class="csm-suggestions" id="clientSuggestions"></div>
                <div class="pagination" id="suggestionPagination" aria-label="Client suggestions pagination"></div>
                <div class="csm-section-footer"><button class="clear-filter" id="toggleSuggestions" type="button">View All Suggestions</button></div>
            </section>

            <section class="card csm-section csm-recent-section">
                <div class="card-header">
                    <div class="card-title"><div class="title-icon"><i class="fa-solid fa-clock-rotate-left"></i></div><div><h3>Recent CSM Responses</h3><p id="recentSummary">Latest completed evaluations</p></div></div>
                </div>
                <div class="table-wrapper csm-table-wrap">
                    <table class="csm-table csm-recent-table">
                        <thead><tr><th>Date</th><th>Client Type</th><th>Region</th><th>Service</th><th>Overall Rating</th><th>Status</th></tr></thead>
                        <tbody id="recentResponses"></tbody>
                    </table>
                </div>
                <div class="pagination" id="recentPagination" aria-label="Recent CSM response pagination"></div>
            </section>
        </div>
    </main>
</div>

<script>
    window.BASE_URL = <?= json_encode(BASE_URL) ?>;
    window.API_URL = <?= json_encode(API_URL) ?>;
</script>
<script src="<?= BASE_URL ?>assets/js/csm-dashboard/csm-dashboard.js" type="module"></script>
</body>
</html>