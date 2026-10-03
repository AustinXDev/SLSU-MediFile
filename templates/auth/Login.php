<?php
require_once __DIR__ . '/../../config/init.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SLSU-MEDIFILE — Sign In</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/login.css">
</head>
<body>

<div class="shell">

  <?php require __DIR__ . '/partials/BrandPanel.php'; ?>

  <!-- ================= RIGHT: LOGIN FORM ================= -->
  <section class="form-panel">
    <div class="form-card">

      <div class="mobile-brand" style="display:none">
        <!-- shown only on mobile via media query below -->
      </div>

      <span class="eyebrow">Welcome Back</span>
      <h2>Sign in to SLSU-Health Record</h2>
      <p class="lede">Access your secure medical information portal.</p>

      <form id="loginForm" novalidate>
        <div class="field" id="userField">
          <label for="username">Username or Email</label>
          <div class="input-wrap">
            <svg class="leading-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <input type="text" id="username" name="username" placeholder="juan.delacruz@slsu.edu.ph" autocomplete="username">
          </div>
          <div class="error-msg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
            Please enter your username or email.
          </div>
        </div>

        <div class="field" id="passField">
          <label for="password">Password</label>
          <div class="input-wrap">
            <svg class="leading-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
            <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" style="padding-right:44px;">
            <button type="button" class="toggle-pass" id="togglePass" aria-label="Show password">
              <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          <div class="error-msg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
            Please enter your password.
          </div>
        </div>

        <div class="row-between">
          <a href="<?= BASE_URL ?>forgotpassword" class="forgot-link">Forgot Password?</a>
        </div>

        <button type="submit" class="btn-primary" id="login-btn">
          Sign In
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </button>

        <div class="secure-note">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
          Your information is protected and securely encrypted.
        </div>
      </form>

      <div class="divider-help">
        Need an account? <a href="#">Contact the Medical Services Office</a>
      </div>

    </div>
  </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    window.APP_ENV = {
        BASE_URL: <?= json_encode(BASE_URL) ?>,
        API_URL: <?= json_encode($_ENV['APP_API'] ?? '') ?>
    };
</script>

<script
    type="module"
    src="<?= BASE_URL ?>assets/js/auth/login/main.js">
</script>
<script src="<?= BASE_URL ?>assets/js/components/modal.js"></script>
</body>
</html>