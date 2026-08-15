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

  <!-- ================= LEFT: BRAND PANEL ================= -->
  <section class="brand-panel">

    <!-- 
      Image Overlay
    -->
    <div style="position: absolute; left: 0; bottom: 0; right: 0; opacity: 15%;">
      <img width="100%" src="<?= BASE_URL ?>assets\images\SLSU-SCHOOL.png" alt="SLSU-SCHOOL">
    </div>

    <div class="bg-pattern" aria-hidden="true">
      <svg viewBox="0 0 900 1000" preserveAspectRatio="xMidYMid slice">
        <defs>
          <pattern id="grid" width="60" height="60" patternUnits="userSpaceOnUse">
            <path d="M60 0H0V60" fill="none" stroke="#FFFFFF" stroke-width="0.6"/>
          </pattern>
        </defs>
        <rect width="900" height="1000" fill="url(#grid)" opacity="0.35"/>
        <!-- large outline cross shapes, offset -->
        <g stroke="#FFFFFF" stroke-width="1.4" fill="none" opacity="0.55">
          <path d="M120 260 h70 v-70 h60 v70 h70 v60 h-70 v70 h-60 v-70 h-70 z"/>
          <path d="M620 620 h50 v-50 h44 v50 h50 v44 h-50 v50 h-44 v-50 h-50 z"/>
          <path d="M700 120 h36 v-36 h32 v36 h36 v32 h-36 v36 h-32 v-36 h-36 z"/>
        </g>
        <!-- soft dotted document/file lines -->
        <g stroke="#FFFFFF" stroke-width="1" opacity="0.3">
          <rect x="470" y="760" width="150" height="190" rx="14"/>
          <line x1="495" y1="805" x2="595" y2="805"/>
          <line x1="495" y1="835" x2="595" y2="835"/>
          <line x1="495" y1="865" x2="565" y2="865"/>
        </g>
        <circle cx="80" cy="720" r="120" fill="none" stroke="#FFFFFF" stroke-width="1" opacity="0.25"/>
        <circle cx="830" cy="470" r="90" fill="none" stroke="#FFFFFF" stroke-width="1" opacity="0.22"/>
      </svg>
    </div>

    <div class="brand-top">
      <div class="logo-row">
        <div>
          <img witdh="50" height="50" src="<?= BASE_URL ?>assets/images/SLSU-LOGO.png" alt="SLSU-LOGO">
        </div>
        <div class="logo-word">SLSU-Health Record<span>Medical File Management</span></div>
      </div>

      <div class="brand-copy">
        <h1>Every record, <Span style="color:#2CB63A;">safely in one place.</Span></h1>
        <div class="subtitle">SLSU Medical Information and File Management System</div>
        <p>A secure and centralized platform for managing medical information and healthcare records across the university.</p>
      </div>
    </div>

    <div class="brand-bottom">
      <div class="feature-list">
        <div class="feature-item">
          <span class="dot-ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="#BFE3DB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z"/></svg>
          </span>
          <span>End-to-end encrypted medical records</span>
        </div>
        <div class="feature-item">
          <span class="dot-ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="#BFE3DB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h11l5 5v11a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z"/><path d="M14 4v5h5"/></svg>
          </span>
          <span>Centralized student health files</span>
        </div>
        <div class="feature-item">
          <span class="dot-ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="#BFE3DB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
          </span>
          <span>Real-time access for authorized staff</span>
        </div>
      </div>

      <div class="panel-footer">
        <span>© 2026 Southern Luzon State University</span>
        <span>Medical Services Office</span>
      </div>
    </div>
  </section>

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
          <label class="remember">
            <input type="checkbox" id="remember">
            <span class="checkbox">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            Remember me
          </label>
          <a href="#" class="forgot-link">Forgot Password?</a>
        </div>

        <button type="submit" class="btn-primary">
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
</body>
</html>