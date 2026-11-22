const DEFAULT_EVENTS = [
  "mousemove",
  "mousedown",
  "keydown",
  "scroll",
  "touchstart",
  "click",
];
const TRANSITION_MS = 180;

let settings = null;
let dialog = null;
let idleTimer = null;
let countdownTimer = null;
let transitionTimer = null;
let lastActivityAt = 0;
let countdownDeadline = 0;
let isInitialized = false;
let isLoggingOut = false;
let warningOpen = false;
let previousFocus = null;
let eventListeners = [];

function normalizedSettings(options = {}) {
  const idleTimeout = Number(options.idleTimeout ?? 3 * 60 * 1000);
  const countdownDuration = Number(options.countdownDuration ?? 30);

  return {
    enabled: options.enabled !== false,
    idleTimeout:
      Number.isFinite(idleTimeout) && idleTimeout > 0
        ? idleTimeout
        : 15 * 60 * 1000,
    countdownDuration:
      Number.isFinite(countdownDuration) && countdownDuration > 0
        ? countdownDuration
        : 30,
    events: Array.isArray(options.events)
      ? [
          ...new Set(
            options.events.filter((event) => typeof event === "string"),
          ),
        ]
      : DEFAULT_EVENTS,
    onLogout: options.onLogout,
  };
}

function createDialog() {
  const modal = document.createElement("section");
  modal.className = "auto-logout";
  modal.hidden = true;
  modal.innerHTML = `
    <div class="auto-logout__backdrop"></div>
    <div
      class="auto-logout__dialog"
      role="dialog"
      aria-modal="true"
      aria-labelledby="autoLogoutTitle"
      aria-describedby="autoLogoutMessage"
      tabindex="-1"
    >
      <div class="auto-logout__icon" aria-hidden="true">
        <i class="fa-solid fa-shield-halved"></i>
      </div>
      <h2 id="autoLogoutTitle">Are You Still There?</h2>
      <p id="autoLogoutMessage">
        You've been inactive for a while. For your security, you'll be
        automatically logged out soon.
      </p>
      <div class="auto-logout__countdown" aria-live="off">
        <span
          class="auto-logout__countdown-value"
          data-countdown
          aria-live="polite"
          aria-atomic="true"
        >30</span>
        <span class="auto-logout__countdown-unit">seconds</span>
      </div>
      <p class="auto-logout__status" data-status aria-live="polite"></p>
      <div
        class="auto-logout__progress"
        role="progressbar"
        aria-label="Time remaining before automatic logout"
        aria-valuemin="0"
        aria-valuemax="30"
        aria-valuenow="30"
        data-progress
      >
        <span data-progress-bar></span>
      </div>
      <div class="auto-logout__actions">
        <button class="auto-logout__stay" type="button" data-stay>
          Stay Logged In
        </button>
        <button class="auto-logout__signout" type="button" data-signout>
          Log Out Now
        </button>
      </div>
    </div>
  `;

  document.body.append(modal);
  dialog = modal;
  dialog.querySelector("[data-stay]").addEventListener("click", stayLoggedIn);
  dialog
    .querySelector("[data-signout]")
    .addEventListener("click", () => startLogout("manual"));
  dialog.addEventListener("keydown", trapDialogFocus);
}

function setStatus(message) {
  const status = dialog?.querySelector("[data-status]");
  if (status) {
    status.textContent = message;
    status.dataset.message = "true";
  }
}

function setDialogButtonsDisabled(disabled) {
  dialog?.querySelectorAll("button").forEach((button) => {
    button.disabled = disabled;
  });
}

function trapDialogFocus(event) {
  if (!dialog || dialog.hidden) return;

  if (event.key === "Escape") {
    event.preventDefault();
    event.stopPropagation();
    return;
  }

  if (event.key !== "Tab") return;
  const buttons = [...dialog.querySelectorAll("button:not(:disabled)")];
  if (!buttons.length) {
    event.preventDefault();
    return;
  }

  const first = buttons[0];
  const last = buttons[buttons.length - 1];
  if (event.shiftKey && document.activeElement === first) {
    event.preventDefault();
    last.focus();
  } else if (!event.shiftKey && document.activeElement === last) {
    event.preventDefault();
    first.focus();
  }
}

function closeWarning(restoreFocus = true) {
  if (!dialog || !warningOpen) return;

  warningOpen = false;
  window.clearTimeout(countdownTimer);
  window.clearTimeout(transitionTimer);
  dialog.classList.remove("is-visible");
  transitionTimer = window.setTimeout(() => {
    if (dialog && !dialog.classList.contains("is-visible")) {
      dialog.hidden = true;
    }
  }, TRANSITION_MS);

  if (restoreFocus && previousFocus?.isConnected) {
    previousFocus.focus();
  }
  previousFocus = null;
}

function showWarning() {
  if (!dialog || isLoggingOut) return;

  window.clearTimeout(idleTimer);
  warningOpen = true;
  previousFocus = document.activeElement;
  dialog.hidden = false;
  dialog.classList.remove("is-visible");
  setDialogButtonsDisabled(false);
  const status = dialog.querySelector("[data-status]");
  status.dataset.message = "false";
  countdownDeadline = Date.now() + settings.countdownDuration * 1000;
  updateCountdown();
  requestAnimationFrame(() => dialog?.classList.add("is-visible"));
  dialog.querySelector("[data-stay]").focus();
}

function updateCountdown() {
  if (!dialog || !warningOpen || isLoggingOut) return;

  const remainingMs = Math.max(0, countdownDeadline - Date.now());
  const remainingSeconds = Math.ceil(remainingMs / 1000);
  const fraction = Math.max(
    0,
    Math.min(1, remainingMs / (settings.countdownDuration * 1000)),
  );
  const countdown = dialog.querySelector("[data-countdown]");
  const progress = dialog.querySelector("[data-progress]");
  const progressBar = dialog.querySelector("[data-progress-bar]");

  countdown.textContent = String(remainingSeconds);
  const status = dialog.querySelector("[data-status]");
  if (status.dataset.message !== "true") {
    status.textContent = `Your session will expire in ${remainingSeconds} second${remainingSeconds === 1 ? "" : "s"}.`;
  }
  progress.setAttribute(
    "aria-valuenow",
    String(Math.min(settings.countdownDuration, remainingSeconds)),
  );
  progress.setAttribute("aria-valuemax", String(settings.countdownDuration));
  progressBar.style.transform = `scaleX(${fraction})`;
  dialog.querySelector(".auto-logout__countdown-unit").textContent =
    remainingSeconds === 1 ? "second" : "seconds";

  if (remainingMs === 0) {
    startLogout("timeout");
    return;
  }

  countdownTimer = window.setTimeout(
    updateCountdown,
    Math.min(1000, remainingMs),
  );
}

function checkDeadline() {
  if (!isInitialized || isLoggingOut) return;

  if (warningOpen) {
    updateCountdown();
    return;
  }

  const remaining = lastActivityAt + settings.idleTimeout - Date.now();
  if (remaining <= 0) {
    showWarning();
    return;
  }

  window.clearTimeout(idleTimer);
  idleTimer = window.setTimeout(checkDeadline, remaining);
}

function resetInactivityTimer() {
  if (!isInitialized || isLoggingOut) return;

  if (warningOpen) {
    closeWarning();
  }
  lastActivityAt = Date.now();
  checkDeadline();
}

function handleUserActivity() {
  if (dialog && !dialog.hidden) return;
  resetInactivityTimer();
}

function stayLoggedIn() {
  if (!warningOpen || isLoggingOut) return;
  closeWarning();
  resetInactivityTimer();
}

async function startLogout(reason) {
  if (isLoggingOut) return;
  if (typeof settings.onLogout !== "function") {
    setStatus("Sign-out is unavailable. Please use the Logout button.");
    console.error("AutoLogout requires an onLogout callback.");
    return;
  }

  isLoggingOut = true;
  cleanupTimers();

  if (warningOpen) {
    setDialogButtonsDisabled(true);
    setStatus("Signing you out...");
  }

  try {
    await settings.onLogout(reason);
  } catch (error) {
    console.error("Unable to sign out after inactivity.", error);
    isLoggingOut = false;
    if (warningOpen) {
      setDialogButtonsDisabled(false);
      setStatus(
        "Unable to sign out. Please try again or use the Logout button.",
      );
    }
  }
}

function cleanupTimers() {
  window.clearTimeout(idleTimer);
  window.clearTimeout(countdownTimer);
  window.clearTimeout(transitionTimer);
  idleTimer = null;
  countdownTimer = null;
  transitionTimer = null;
}

function cleanup() {
  cleanupTimers();
  eventListeners.forEach(([target, type, listener, options]) => {
    target.removeEventListener(type, listener, options);
  });
  eventListeners = [];
  document.removeEventListener("visibilitychange", checkDeadline);
  window.removeEventListener("pageshow", checkDeadline);
  dialog?.remove();
  dialog = null;
  warningOpen = false;
  isInitialized = false;
}

function listen(target, type, listener, options) {
  target.addEventListener(type, listener, options);
  eventListeners.push([target, type, listener, options]);
}

export const AutoLogout = {
  init(options = {}) {
    cleanup();
    settings = normalizedSettings(options);
    isLoggingOut = false;

    if (!settings.enabled || !document.body) return;
    if (typeof settings.onLogout !== "function") {
      throw new TypeError("AutoLogout.init requires an onLogout callback.");
    }

    createDialog();
    isInitialized = true;
    lastActivityAt = Date.now();

    settings.events.forEach((event) => {
      listen(document, event, handleUserActivity, { passive: true });
    });
    listen(document, "visibilitychange", checkDeadline);
    listen(window, "pageshow", checkDeadline);
    checkDeadline();
  },

  destroy() {
    cleanup();
    settings = null;
    isLoggingOut = false;
  },
};
