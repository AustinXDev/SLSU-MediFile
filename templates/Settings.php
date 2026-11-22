<?php
require_once __DIR__ . '/../config/init.php';

use App\Middleware\AdminMiddleware;
use App\Session\SessionManager;

$session = new SessionManager();
(new AdminMiddleware($session))->requireAuth();

$role = trim((string) ($session->get('role') ?? ''));
$username = (string) ($session->get('admin_username') ?? '');
$initials = strtoupper(substr($username !== '' ? $username : 'A', 0, 2));
$isSuperAdmin = $role === 'Super Admin';
$canExport = in_array($role, ['Super Admin', 'Administrator'], true);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLSU-MEDIFILE | Settings</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/users.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/autoLogout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/settings.css">
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
            <div class="admin-avatar"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="admin-info">
                <strong><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></strong>
                <span><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></span>
                <small><i></i>Online</small>
            </div>
        </div>
        <nav class="navigation">
            <p class="nav-label">MAIN MENU</p>
            <a href="<?= BASE_URL ?>dashboard" class="nav-item"><span class="nav-icon"><i class="fa-solid fa-house"></i></span><span>Dashboard</span></a>
            <a href="<?= BASE_URL ?>patients" class="nav-item"><span class="nav-icon"><i class="fa-solid fa-user-injured"></i></span><span>Patient Records</span></a>
            <?php if ($isSuperAdmin): ?>
                <a href="<?= BASE_URL ?>users" class="nav-item"><span class="nav-icon"><i class="fa-solid fa-user-nurse"></i></span><span>Users / Staff</span></a>
            <?php endif; ?>
            <?php if (in_array($role, ['Super Admin', 'Administrator'], true)): ?>
                <a href="<?= BASE_URL ?>csmdashboard" class="nav-item"><span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span><span>Reports &amp; Analytics</span></a>
            <?php endif; ?>
            <?php if ($isSuperAdmin): ?>
                <a href="<?= BASE_URL ?>activitylogs" class="nav-item"><span class="nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></span><span>Activity Logs</span></a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>settings" class="nav-item active" aria-current="page"><span class="nav-icon"><i class="fa-solid fa-gear"></i></span><span>Settings</span></a>
        </nav>
        <div class="sidebar-bottom">
            <button type="button" class="logout" data-logout><span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span><span>Logout</span></button>
        </div>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <main class="main">
        <header class="top-header">
            <div class="header-left">
                <button class="mobile-menu" onclick="openSidebar()" aria-label="Open navigation"><i class="fa-solid fa-bars"></i></button>
                <div><h2>Settings</h2><p>Manage your account and system preferences.</p></div>
            </div>
            <div class="header-right">
                <button class="header-profile" aria-label="Current account">
                    <div class="profile-avatar"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="profile-details"><strong><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></span></div>
                    <span class="profile-arrow"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
            </div>
        </header>

        <div class="content settings-content">
            <section class="page-heading">
                <div>
                    <span class="eyebrow-label"><i class="fa-solid fa-sliders"></i> Preferences &amp; administration</span>
                    <h1>Settings</h1>
                    <p>Manage your account, application configuration, and data.</p>
                </div>
            </section>

            <section class="card settings-card" aria-labelledby="profileHeading">
                <div class="settings-card-heading">
                    <div><h2 id="profileHeading">Account &amp; Profile</h2><p>Manage your personal account information.</p></div>
                    <i class="fa-solid fa-user-gear" aria-hidden="true"></i>
                </div>
                <form id="profileForm" class="settings-form">
                    <div class="settings-section-label">Personal Information</div>
                    <div class="settings-grid">
                        <label class="form-field"><span>First Name</span><input name="first_name" id="profileFirstName" required maxlength="100" autocomplete="given-name"></label>
                        <label class="form-field"><span>Last Name</span><input name="last_name" id="profileLastName" required maxlength="100" autocomplete="family-name"></label>
                        <label class="form-field"><span>Username</span><input name="username" id="profileUsername" required maxlength="50" autocomplete="username"></label>
                        <label class="form-field"><span>Email</span><input name="email" id="profileEmail" type="email" required maxlength="150" autocomplete="email"></label>
                    </div>
                    <div class="settings-section-label">Account Information</div>
                    <div class="settings-grid settings-readonly-grid">
                        <div class="settings-readonly"><span>Role</span><strong id="profileRole">Loading...</strong></div>
                        <div class="settings-readonly"><span>Account Status</span><strong id="profileStatus">Loading...</strong></div>
                        <div class="settings-readonly settings-last-login"><span>Last Login</span><strong id="profileLastLogin">Loading...</strong></div>
                    </div>
                    <div class="settings-form-actions">
                        <button class="secondary-button" type="button" id="togglePasswordForm"><i class="fa-solid fa-lock"></i> Change Password</button>
                        <button class="primary-button" type="submit" id="saveProfileButton"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
                    </div>
                </form>
                <form id="passwordForm" class="settings-password-form" hidden>
                    <div class="settings-section-label">Change Password</div>
                    <div class="settings-grid">
                        <label class="form-field"><span>Current Password</span><input name="current_password" type="password" autocomplete="current-password" required></label>
                        <label class="form-field"><span>New Password</span><input name="new_password" type="password" autocomplete="new-password" minlength="8" required></label>
                        <label class="form-field"><span>Confirm New Password</span><input name="confirm_password" type="password" autocomplete="new-password" minlength="8" required></label>
                    </div>
                    <div class="settings-form-actions">
                        <button class="secondary-button" type="button" id="cancelPasswordForm">Cancel</button>
                        <button class="primary-button" type="submit" id="changePasswordButton">Update Password</button>
                    </div>
                </form>
            </section>

            <!--
            <?php if ($isSuperAdmin): ?>
                <section class="card settings-card" aria-labelledby="systemHeading">
                    <div class="settings-card-heading">
                        <div><h2 id="systemHeading">System Application</h2><p>Manage application identity and branding.</p></div>
                        <i class="fa-solid fa-building-gear" aria-hidden="true"></i>
                    </div>
                    <form id="systemSettingsForm" class="settings-form">
                        <div class="settings-grid settings-grid-single">
                            <label class="form-field"><span>Application Name</span><input name="application_name" id="applicationName" required maxlength="150"></label>
                            <label class="form-field"><span>Institution Name</span><input name="institution_name" id="institutionName" required maxlength="200"></label>
                            <label class="form-field"><span>Developer Contact Email</span><input name="developer_contact_email" id="developerEmail" type="email" maxlength="254"></label>
                        </div>
                        <div class="logo-setting">
                            <div><span class="settings-field-label">School Logo</span><p>PNG, JPEG, or WebP, up to 2 MB.</p></div>
                            <img id="schoolLogoPreview" src="<?= BASE_URL ?>assets/images/SLSU-LOGO.png" alt="Current school logo">
                            <label class="secondary-button upload-logo-button" for="schoolLogoInput"><i class="fa-solid fa-arrow-up-from-bracket"></i> Change Logo</label>
                            <input id="schoolLogoInput" type="file" accept="image/png,image/jpeg,image/webp" hidden>
                        </div>
                        <div class="settings-form-actions">
                            <button class="primary-button" type="submit" id="saveSystemSettingsButton"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
                        </div>
                    </form>
                </section>
            <?php endif; ?>

            <?php if ($isSuperAdmin || $canExport): ?>
                <section class="card settings-card settings-data-card" aria-labelledby="dataHeading">
                    <div class="settings-card-heading">
                        <div><h2 id="dataHeading">Data &amp; Backup</h2><p>Protect, manage, and export system data.</p></div>
                        <i class="fa-solid fa-database" aria-hidden="true"></i>
                    </div>
                    <?php if ($isSuperAdmin): ?>
                        <div class="settings-subsection">
                            <div class="settings-subsection-heading">
                                <div><h3>Backup Archive</h3><p>Create and manage private database backups.</p></div>
                                <button class="primary-button" type="button" id="createBackupButton"><i class="fa-solid fa-box-archive"></i> Create Backup</button>
                            </div>
                            <div class="settings-table-heading"><h4>Backup History</h4><span id="backupSummary">Loading backups...</span></div>
                            <div class="settings-table-wrapper">
                                <table class="settings-table">
                                    <thead><tr><th>Date &amp; Time</th><th>Type</th><th>Size</th><th>Created By</th><th>Status</th><th>Actions</th></tr></thead>
                                    <tbody id="backupTableBody"><tr><td colspan="6">Loading backup history...</td></tr></tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($canExport): ?>
                        <div class="settings-subsection settings-export-subsection">
                            <div class="settings-subsection-heading">
                                <div><h3>Export Data</h3><p>Export application records for administrative and reporting purposes. Exports are separate from recovery backups.</p></div>
                            </div>
                            <form id="exportForm" class="settings-export-form">
                                <label class="form-field"><span>Select Data</span><select id="exportDataset" required><option value="">Loading datasets...</option></select></label>
                                <label class="form-field"><span>Export Format</span><select id="exportFormat"><option value="csv">CSV</option></select></label>
                                <button class="primary-button" type="submit"><i class="fa-solid fa-file-export"></i> Export Data</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
                    -->
        </div>
    </main>
</div>
<script>
    window.APP_ENV = {APP_URL: <?= json_encode(API_URL) ?>};
    window.SETTINGS_CONFIG = {
        baseUrl: <?= json_encode(BASE_URL) ?>,
        canManageSystem: <?= json_encode($isSuperAdmin) ?>,
        canManageBackups: <?= json_encode($isSuperAdmin) ?>,
        canExport: <?= json_encode($canExport) ?>
    };
</script>
<script type="module" src="<?= BASE_URL ?>assets/js/settings/main.js"></script>
<script src="<?= BASE_URL ?>assets/js/components/modal.js"></script>
</body>
</html>
