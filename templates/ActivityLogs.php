<?php

require_once __DIR__ . '/../config/init.php';

use App\Middleware\AdminMiddleware;
use App\Session\SessionManager;

$session = new SessionManager();
(new AdminMiddleware($session))->requireAuth();

$role = trim((string) ($session->get('role') ?? ''));
if (!in_array($role, ['Super Admin', 'Administrator'], true)) {
    http_response_code(403);
    echo 'Access denied.';
    exit;
}

$username = (string) ($session->get('admin_username') ?? '');
$roleName = (string) ($session->get('role') ?? '');
$initials = strtoupper(substr($username !== '' ? $username : 'A', 0, 2));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLSU-MEDIFILE | Activity Logs</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/users.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/activity-logs.css">
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
                <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="admin-info">
                <strong><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></strong>
                <span><?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8') ?></span>
                <small><i></i>Online</small>
            </div>
        </div>

        <nav class="navigation">
            <p class="nav-label">MAIN MENU</p>

            <a href="dashboard" class="nav-item">
                <span class="nav-icon"><i class="fa-solid fa-house"></i></span>
                <span>Dashboard</span>
            </a>

            <a href="patients" class="nav-item">
                <span class="nav-icon"><i class="fa-solid fa-user-injured"></i></span>
                <span>Patient Records</span>
            </a>

            <?php
                if (strtolower(trim($role)) === 'super admin'):
                    ?>

                <a href="users" class="nav-item">
                    <span class="nav-icon"><i class="fa-solid fa-user-nurse"></i></span>
                    <span>Users / Staff</span>
                </a>

            <?php endif; ?>
            
            <?php
                    if (strtolower(trim($role)) === 'super admin' || strtolower(trim($role)) === 'administrator'):
                        ?>

            <a href="csmdashboard" class="nav-item">
                <span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span>
                <span>Reports &amp; Analytics</span>
            </a>

            <?php endif; ?>
            
            <?php
            if (strtolower(trim($role)) === 'super admin'):
                ?>

            <a href="activitylogs" class="nav-item active" aria-current="page">
                <span class="nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                <span>Activity Logs</span>
            </a>

            <?php endif; ?>

            <a href="#" class="nav-item">
                <span class="nav-icon"><i class="fa-solid fa-gear"></i></span>
                <span>Settings</span>
            </a>

        </nav>

        <div class="sidebar-bottom">
            <button type="button" class="logout" data-logout>
                <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                <span>Logout</span>
            </button>
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
                    <h2>Activity Logs</h2>
                    <p>Welcome back, <strong>Administrator</strong></p>
                </div>
            </div>
            <div class="header-right">
                <button class="header-button notification-button" aria-label="Notifications">
                    <i class="fa-solid fa-bell"></i><i></i>
                </button>
                <button class="header-profile" aria-label="Administrator profile">
                    <div class="profile-avatar"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="profile-details">
                        <strong><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></strong>
                        <span><?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <span class="profile-arrow"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
            </div>
        </header>

        <div class="content activity-content">
            <section class="page-heading">
                <div>
                    <span class="eyebrow-label"><i class="fa-solid fa-clock-rotate-left"></i> System monitoring</span>
                    <h1>Activity Logs</h1>
                    <p>Monitor and review recent system activities and user actions.</p>
                </div>
            </section>

            <div class="activity-tabs" role="tablist" aria-label="Activity views">
                <button class="activity-tab active" id="logsTab" type="button" role="tab"
                    aria-selected="true" aria-controls="activityPanel" data-activity-tab="logs">
                    <i class="fa-solid fa-list"></i> Activity Logs
                </button>
                <button class="activity-tab" id="patientsTab" type="button" role="tab"
                    aria-selected="false" aria-controls="activityPanel" data-activity-tab="patients">
                    <i class="fa-solid fa-user-injured"></i> Recent Patients
                </button>
            </div>

            <form class="card user-toolbar activity-toolbar" id="activityFilters" aria-label="Activity log filters">
                <label class="user-search activity-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input id="activitySearch" type="search" placeholder="Search activities..." aria-label="Search activities">
                </label>

                <label class="filter-field csm-date-range">
                    <span>Date Range</span>
                    <select id="dateRange">
                        <option value="all">All Dates</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="7">Last 7 Days</option>
                        <option value="30">Last 30 Days</option>
                        <option value="custom">Custom Date Range</option>
                    </select>
                    <span class="csm-date-inputs activity-custom-dates" id="customDates" hidden>
                        <input id="dateFrom" type="date" aria-label="Start date">
                        <span aria-hidden="true">to</span>
                        <input id="dateTo" type="date" aria-label="End date">
                    </span>
                </label>

                <label class="filter-field">
                    <span>Activity</span>
                    <select id="activityFilter">
                        <option value="">All Activities</option>
                    </select>
                </label>

                <label class="filter-field">
                    <span>Module</span>
                    <select id="moduleFilter">
                        <option value="">All Modules</option>
                    </select>
                </label>

                <div class="csm-filter-actions activity-filter-actions">
                    <button class="primary-button" type="submit">
                        <i class="fa-solid fa-filter"></i> Apply Filters
                    </button>
                    <button class="clear-filter" id="resetFilters" type="button">Reset</button>
                </div>
            </form>

            <div class="activity-summary" aria-label="Activity summary">
                <article class="card activity-summary-card">
                    <div class="activity-summary-icon"><i class="fa-solid fa-list-check"></i></div>
                    <div><p>Total Activities</p><strong id="totalActivities">—</strong></div>
                </article>
                <article class="card activity-summary-card">
                    <div class="activity-summary-icon"><i class="fa-solid fa-calendar-day"></i></div>
                    <div><p>Today's Activities</p><strong id="todayActivities">—</strong></div>
                </article>
                <article class="card activity-summary-card">
                    <div class="activity-summary-icon"><i class="fa-solid fa-users"></i></div>
                    <div><p>Active Users</p><strong id="activeUsers">—</strong></div>
                </article>
                <article class="card activity-summary-card activity-recent-card">
                    <div class="activity-summary-icon"><i class="fa-solid fa-clock"></i></div>
                    <div><p>Most Recent Activity</p><strong id="recentActivity">—</strong></div>
                </article>
            </div>

            <section class="card users-card activity-card" id="activityPanel" role="tabpanel" aria-labelledby="logsTab">
                <div class="users-card-header">
                    <div>
                        <h3 id="activityTableTitle">Activity Logs</h3>
                        <p class="record-count" id="activityResultSummary" aria-live="polite">Loading activity...</p>
                    </div>
                </div>

                <div class="user-table-wrapper activity-table-wrapper">
                    <table id="activityTable">
                        <thead id="activityTableHead"></thead>
                        <tbody id="activityTableBody"></tbody>
                    </table>
                    <div class="empty-state" id="activityEmptyState" hidden>
                        <div class="empty-state-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        <h3>No activity records found.</h3>
                        <p>Try adjusting your search or filters.</p>
                    </div>
                </div>

                <div class="activity-pagination">
                    <p id="activityPaginationSummary"></p>
                    <div class="pagination" id="activityPagination" aria-label="Activity pagination"></div>
                </div>
            </section>
            <p class="activity-error" id="activityError" role="alert" hidden></p>
        </div>
    </main>
</div>

<div class="modal-backdrop activity-modal-backdrop" id="activityModalBackdrop" hidden>
    <section class="modal-panel view-panel activity-modal" role="dialog" aria-modal="true"
        aria-labelledby="activityModalTitle">
        <div class="modal-header">
            <div>
                <span class="eyebrow-label modal-eyebrow"><i class="fa-solid fa-clock-rotate-left"></i> Audit record</span>
                <h3 id="activityModalTitle">Activity Details</h3>
            </div>
            <button type="button" class="close-modal" id="closeActivityModal" aria-label="Close activity details">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="view-grid" id="activityDetails"></div>
        <div class="modal-footer">
            <button type="button" class="secondary-button" id="dismissActivityModal">Close</button>
        </div>
    </section>
</div>

<script>
    window.APP_ENV = {
        APP_URL: <?= json_encode(API_URL) ?>,
        BASE_URL: <?= json_encode(BASE_URL) ?>
    };
    window.APP_API = <?= json_encode(API_URL) ?>;
    window.ACTIVITY_LOGS_PAGE_URL = <?= json_encode(BASE_URL . 'activitylogs') ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script type="module" src="<?= BASE_URL ?>assets/js/activity-logs/index.js"></script>
</body>
</html>
