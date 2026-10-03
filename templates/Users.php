<?php
require_once __DIR__ . '/../config/init.php';

use App\Middleware\AdminMiddleware;
use App\Session\SessionManager;

$session = new SessionManager();
$middleware = new AdminMiddleware($session);
$middleware->requireAuth();

$role = trim((string) ($session->get('role') ?? ''));
if (!in_array($role, ['Super Admin', 'Administrator'], true)) {
    http_response_code(403);
    echo 'Access denied.';
    exit;
}

$username = (string) ($session->get('admin_username') ?? '');
$roleName = (string) ($session->get('role') ?? '');
$initials = strtoupper(substr($username !== '' ? $username : 'A', 0, 2));

$pageTitle = 'User & Staff Accounts';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLSU-MEDIFILE | <?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/users.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
<div class="dashboard">
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

            <a href="users" class="nav-item active" aria-current="page">
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

            <a href="<?= BASE_URL ?>activitylogs" class="nav-item">
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
            <a href="#" class="logout">
                <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                <span>Logout</span>
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
                    <h2>User &amp; Staff Accounts</h2>
                    <p>Welcome back, <strong>Administrator</strong></p>
                </div>
            </div>

            <div class="header-right">
                <button class="header-button notification-button" aria-label="Notifications">
                    <i class="fa-solid fa-bell"></i>
                    <i></i>
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

        <div class="content users-content">
            <section class="page-heading">
                <div>
                    <span class="eyebrow-label"><i class="fa-solid fa-user-shield"></i> Access management</span>
                    <h1>User &amp; Staff Accounts</h1>
                    <p>Manage system users, staff accounts, roles, and account access.</p>
                </div>

                <button class="primary-button" id="addUserButton" type="button">
                    <i class="fa-solid fa-plus"></i> Add User
                </button>
            </section>

            <section class="card user-toolbar" aria-label="User search and filters">
                <div class="user-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input id="userSearch" type="search" placeholder="Search by name, username, or email" aria-label="Search user accounts">
                </div>

                <label class="filter-field">
                    <span>Role</span>
                    <select id="roleFilter">
                        <option value="">All Roles</option>
                    </select>
                </label>

                <label class="filter-field">
                    <span>Status</span>
                    <select id="statusFilter">
                        <option value="">All Statuses</option>
                    </select>
                </label>

                <button class="clear-filter" type="button" id="clearFiltersButton">Clear filters</button>
            </section>

            <section class="card users-card">
                <div class="users-card-header">
                    <div>
                        <h3>All User Accounts</h3>
                        <p class="record-count" id="resultSummary">Loading accounts...</p>
                    </div>
                </div>

                <div class="user-table-wrapper">
                    <div class="table-loading" id="tableLoading" hidden>
                        <div class="spinner"></div>
                        <span>Loading accounts...</span>
                    </div>

                    <table id="usersTable">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username / Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody id="usersBody"></tbody>
                    </table>

                    <div class="empty-state" id="emptyState" hidden>
                        <div class="empty-state-icon"><i class="fa-solid fa-user-gear"></i></div>
                        <h3>No user accounts found.</h3>
                        <p>Try adjusting your search or filters.</p>
                    </div>
                </div>

                <div class="pagination" id="pagination"></div>
            </section>
        </div>
    </main>
</div>

<div class="modal-backdrop" id="userModalBackdrop" hidden>
    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="userModalTitle">
        <div class="modal-header">
            <div>
                <span class="eyebrow-label modal-eyebrow"><i class="fa-solid fa-user-shield"></i> Administrator access</span>
                <h3 id="userModalTitle">Create User Account</h3>
            </div>
            <button type="button" class="close-modal" data-close="userModal" aria-label="Close dialog">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="userForm" novalidate>
            <div class="form-grid">
                <label class="form-field">
                    <span>First Name</span>
                    <input type="text" name="first_name" id="first_name" placeholder="First Name">
                </label>

                <label class="form-field">
                    <span>Last Name</span>
                    <input type="text" name="last_name" id="last_name" placeholder="Last Name">
                </label>

                <label class="form-field full-width">
                    <span>Username</span>
                    <input type="text" name="username" id="username" placeholder="Username">
                </label>

                <label class="form-field full-width">
                    <span>Email</span>
                    <input type="email" name="email" id="email" placeholder="user@example.com">
                </label>

                <label class="form-field">
                    <span>Role</span>
                    <select name="role" id="roleSelect">
                        <option value="">Select role</option>
                    </select>
                </label>

                <label class="form-field">
                    <span>Status</span>
                    <select name="status" id="statusSelect">
                        <option value="">Select status</option>
                    </select>
                </label>

                <div class="password-section full-width" id="passwordSection">
                    <div class="helper-box">
                        <strong>Password requirements</strong>
                        <ul>
                            <li data-password-rule="length" data-met="false">○ At least 8 characters</li>
                            <li data-password-rule="uppercase" data-met="false">○ One uppercase letter</li>
                            <li data-password-rule="lowercase" data-met="false">○ One lowercase letter</li>
                            <li data-password-rule="number" data-met="false">○ One number</li>
                            <li data-password-rule="special" data-met="false">○ One special character</li>
                        </ul>
                    </div>

                    <div class="form-grid">
                        <label class="form-field">
                            <span>Password</span>
                            <input type="password" name="password" id="password" placeholder="Enter password">
                        </label>

                        <label class="form-field">
                            <span>Confirm Password</span>
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password">
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="secondary-button" data-close="userModal">Cancel</button>
                <button type="submit" class="primary-button" id="saveUserButton">Save User</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModalBackdrop" hidden>
    <div class="modal-panel view-panel" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">
        <div class="modal-header">
            <div>
                <span class="eyebrow-label modal-eyebrow"><i class="fa-solid fa-eye"></i> Account details</span>
                <h3 id="viewModalTitle">User Account Details</h3>
            </div>
            <button type="button" class="close-modal" data-close="viewModal" aria-label="Close account details">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="view-grid" id="viewAccountDetails"></div>

        <div class="modal-footer">
            <button type="button" class="secondary-button" data-close="viewModal">Close</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="passwordModalBackdrop" hidden>
    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="passwordModalTitle">
        <div class="modal-header">
            <div>
                <span class="eyebrow-label modal-eyebrow"><i class="fa-solid fa-key"></i> Secure password reset</span>
                <h3 id="passwordModalTitle">Reset Password</h3>
            </div>
            <button type="button" class="close-modal" data-close="passwordModal" aria-label="Close reset password modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="passwordForm" novalidate>
            <div class="helper-box">
                <strong>Password requirements</strong>
                <ul>
                    <li data-password-rule="length" data-met="false">○ At least 8 characters</li>
                    <li data-password-rule="uppercase" data-met="false">○ One uppercase letter</li>
                    <li data-password-rule="lowercase" data-met="false">○ One lowercase letter</li>
                    <li data-password-rule="number" data-met="false">○ One number</li>
                    <li data-password-rule="special" data-met="false">○ One special character</li>
                </ul>
            </div>
            <div class="form-grid single-column">
                <label class="form-field full-width">
                    <span>New Password</span>
                    <input type="password" name="new_password" id="new_password" placeholder="Enter new password">
                </label>

                <label class="form-field full-width">
                    <span>Confirm New Password</span>
                    <input type="password" name="confirm_new_password" id="confirm_new_password" placeholder="Confirm new password">
                </label>
            </div>

            <div class="modal-footer">
                <button type="button" class="secondary-button" data-close="passwordModal">Cancel</button>
                <button type="submit" class="primary-button">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="confirmModalBackdrop" hidden>
    <div class="modal-panel confirm-panel" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle">
        <div class="modal-header">
            <div>
                <span class="eyebrow-label modal-eyebrow"><i class="fa-solid fa-triangle-exclamation"></i> Confirmation</span>
                <h3 id="confirmModalTitle">Delete User Account?</h3>
            </div>
            <button type="button" class="close-modal" data-close="confirmModal" aria-label="Close delete confirmation">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <p class="confirm-message">This action cannot be undone.</p>

        <div class="modal-footer">
            <button type="button" class="secondary-button" data-close="confirmModal">Cancel</button>
            <button type="button" class="danger-button" id="confirmDeleteButton">Delete</button>
        </div>
    </div>
</div>

<script>
    window.APP_API = "<?= API_URL ?>";
</script>
<script src="<?= BASE_URL ?>assets/js/components/modal.js"></script>
<script type="module" src="<?= BASE_URL ?>assets/js/users/index.js"></script>
</body>
</html>
