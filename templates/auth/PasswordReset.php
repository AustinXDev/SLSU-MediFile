<?php

require_once __DIR__ . '/../../config/init.php';

$passwordResetMode = $passwordResetMode ?? 'request';
$isResetPage = $passwordResetMode === 'reset';
$token = is_string($_GET['token'] ?? null) ? $_GET['token'] : '';
$pageTitle = $isResetPage ? 'Create a New Password' : 'Forgot your password?';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <title>SLSU-MEDIFILE — <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/login.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/auth-password-reset.css">
</head>
<body class="auth-reset-page">
<div class="shell">
    <?php require __DIR__ . '/partials/BrandPanel.php'; ?>

    <section class="form-panel">
        <div class="form-card auth-reset-card">
            <span class="eyebrow" id="resetEyebrow"><?= $isResetPage ? 'Account security' : 'Account recovery' ?></span>
            <h2 id="resetHeading"><?= $isResetPage ? 'Create a New Password' : 'Forgot your password?' ?></h2>
            <p class="lede" id="resetDescription">
                <?= $isResetPage
                    ? 'Enter a new password for your account.'
                    : "Enter your registered email address and we'll send you a link to reset your password." ?>
            </p>

            <div class="auth-reset-message" id="resetMessage" role="status" aria-live="polite" hidden></div>

            <?php if (!$isResetPage): ?>
                <form id="requestResetForm" novalidate>
                    <div class="field" id="emailField">
                        <label for="resetEmail">Email Address</label>
                        <div class="input-wrap">
                            <svg class="leading-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>
                            </svg>
                            <input id="resetEmail" name="email" type="email" placeholder="Enter your email address"
                                autocomplete="email" required maxlength="254" aria-describedby="emailError">
                        </div>
                        <div class="error-msg" id="emailError">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
                            <span>Please enter a valid email address.</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" id="requestResetButton">
                        Send Reset Link
                    </button>
                </form>
                <button type="button" class="forgot-link auth-reset-retry" id="requestAnotherLink" hidden>
                    Try another email address
                </button>
            <?php else: ?>
                <a class="forgot-link auth-reset-retry" id="requestNewLink" href="<?= BASE_URL ?>forgotpassword" hidden>
                    Request a new reset link
                </a>
                <form id="changePasswordForm" novalidate hidden>
                    <div class="field" id="newPasswordField">
                        <label for="newPassword">New Password</label>
                        <div class="input-wrap">
                            <svg class="leading-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/>
                            </svg>
                            <input type="password" id="newPassword" name="password" placeholder="Enter your new password"
                                autocomplete="new-password" required maxlength="128" aria-describedby="passwordRequirements passwordError">
                            <button type="button" class="toggle-pass" id="toggleNewPassword" aria-label="Show password" aria-pressed="false">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        <div class="error-msg" id="passwordError">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
                            <span>Please meet all password requirements.</span>
                        </div>
                    </div>

                    <div class="field" id="confirmPasswordField">
                        <label for="confirmPassword">Confirm New Password</label>
                        <div class="input-wrap">
                            <svg class="leading-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/>
                            </svg>
                            <input type="password" id="confirmPassword" name="password_confirmation" placeholder="Confirm your new password"
                                autocomplete="new-password" required maxlength="128" aria-describedby="confirmPasswordError">
                            <button type="button" class="toggle-pass" id="toggleConfirmPassword" aria-label="Show password" aria-pressed="false">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        <div class="error-msg" id="confirmPasswordError">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
                            <span>Passwords do not match.</span>
                        </div>
                    </div>

                    <div class="password-requirements" id="passwordRequirements" aria-live="polite" hidden>
                        <p>Password requirements</p>
                        <ul>
                            <li data-requirement="length">At least 8 characters</li>
                            <li data-requirement="uppercase">Contains an uppercase letter</li>
                            <li data-requirement="lowercase">Contains a lowercase letter</li>
                            <li data-requirement="number">Contains a number</li>
                            <li data-requirement="special">Contains one of ! @ # $ % ^ &amp; * </li>
                        </ul>
                    </div>

                    <button type="submit" class="btn-primary" id="changePasswordButton">
                        Change Password
                    </button>
                </form>
            <?php endif; ?>

            <div class="auth-reset-footer" id="resetFooter">
                <a class="forgot-link" href="<?= BASE_URL ?>login" id="backToLogin">Back to Login</a>
            </div>
        </div>
    </section>
</div>

<script>
    window.APP_ENV = {
        BASE_URL: <?= json_encode(BASE_URL) ?>,
        APP_URL: <?= json_encode(API_URL) ?>
    };
    window.PASSWORD_RESET_CONFIG = {
        mode: <?= json_encode($isResetPage ? 'reset' : 'request') ?>,
        token: <?= json_encode($token, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script type="module" src="<?= BASE_URL ?>assets/js/auth/password-reset/main.js"></script>
</body>
</html>
