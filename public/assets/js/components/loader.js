const STYLE_ID = "loader-module-styles";

function injectStyles() {
  if (document.getElementById(STYLE_ID)) return;

  const style = document.createElement("style");
  style.id = STYLE_ID;
  style.textContent = `
  :root {
      --loader-color: #16a34a;
      --loader-bg: rgba(15, 23, 20, 0.55);
      --loader-panel-bg: #ffffff;
      --loader-text-color: #1f2937;
      --loader-size: 44px;
    }
 
    .loader-overlay {
      position: fixed;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--loader-bg);
      backdrop-filter: blur(2px);
      z-index: 9999;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s ease;
    }
 
    .loader-overlay.is-visible {
      opacity: 1;
      pointer-events: all;
    }
 
    .loader-panel {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
      padding: 24px 32px;
      background: var(--loader-panel-bg);
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
      transform: scale(0.95);
      transition: transform 0.2s ease;
    }
 
    .loader-overlay.is-visible .loader-panel {
      transform: scale(1);
    }
 
    .loader-inline {
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }
 
    .loader-spinner {
      width: var(--loader-size);
      height: var(--loader-size);
      border-radius: 50%;
      border: 3px solid color-mix(in srgb, var(--loader-color) 20%, transparent);
      border-top-color: var(--loader-color);
      animation: loader-spin 0.75s linear infinite;
    }
 
    .loader-inline .loader-spinner {
      --loader-size: 20px;
      border-width: 2px;
    }
 
    .loader-text {
      font: 500 14px/1.4 system-ui, -apple-system, "Segoe UI", sans-serif;
      color: var(--loader-text-color);
      text-align: center;
    }
 
    .loader-inline .loader-text {
      font-size: 13px;
    }
 
    @keyframes loader-spin {
      to { transform: rotate(360deg); }
    }
 
    @media (prefers-reduced-motion: reduce) {
      .loader-spinner { animation-duration: 1.5s; }
    }
  `;

  document.head.appendChild(style);
}

export class Loader {
  constructor({
    text = "",
    target = document.body,
    inline = false,
    color,
  } = {}) {
    injectStyles();

    this.target = target;
    this.inline = inline;
    this._visible = false;

    this.el = document.createElement("div");
    this.el.className = inline ? "loader-inline" : "loader-overlay";
    if (color) this.el.style.setProperty("--loader-color", color);

    const spinner = document.createElement("div");
    spinner.className = "loader-spinner";

    if (inline) {
      this.el.appendChild(spinner);
      if (text) {
        const label = document.createElement("span");
        label.className = "loader-text";
        label.textContent = text;
        this.el.appendChild(label);
      }
    } else {
      const panel = document.createElement("div");
      panel.className = "loader-panel";
      panel.appendChild(spinner);
      if (text) {
        const label = document.createElement("div");
        label.className = "loader-text";
        label.textContent = text;
        panel.appendChild(label);
      }
      this.el.appendChild(panel);
    }

    this._textEl = this.el.querySelector(".loader-text");
  }

  setText(text) {
    if (!this._textEl && text) {
      this._textEl = document.createElement(this.inline ? "span" : "div");
      this._textEl.className = "loader-text";
      (this.inline
        ? this.el
        : this.el.querySelector(".loader-panel")
      ).appendChild(this._textEl);
    }
    if (this._textEl) this._textEl.textContent = text;
  }

  show() {
    if (!this.el.isConnected) this.target.appendChild(this.el);
    this.el.offsetHeight;
    this.el.classList.add("is-visible");
    this._visible = true;
    return this;
  }

  hide() {
    this.el.classList.remove("is-visible");
    this._visible = false;
    if (!this.inline) {
      const el = this.el;
      const onEnd = () => {
        el.remove();
        el.removeEventListener("transitionend", onEnd);
      };
      el.addEventListener("transitionend", onEnd);
    } else {
      this.el.remove();
    }
    return this;
  }

  toggle(force) {
    const next = force ?? !this._visible;
    return next ? this.show() : this.hide();
  }

  get isVisible() {
    return this._visible;
  }

  destroy() {
    this.el.remove();
  }

  static async wrap(promise, opts = {}) {
    const loader = new Loader(opts);
    loader.show();
    try {
      return await promise;
    } finally {
      loader.hide();
    }
  }
}
