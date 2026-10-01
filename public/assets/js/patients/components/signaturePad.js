export const signaturePad = {
  canvas: null,
  ctx: null,
  isDrawing: false,
  hasSignature: false,

  init() {
    this.canvas = document.getElementById("patientSignature");

    if (!this.canvas) return;

    this.ctx = this.canvas.getContext("2d");

    this.bindEvents();
  },

  bindEvents() {
    this.canvas.addEventListener("pointerdown", (event) => this.start(event));

    this.canvas.addEventListener("pointermove", (event) => this.draw(event));

    this.canvas.addEventListener("pointerup", () => this.stop());

    this.canvas.addEventListener("pointercancel", () => this.stop());

    this.canvas.addEventListener("pointerleave", () => this.stop());
  },

  resize() {
    if (!this.canvas || !this.ctx) return;

    const rect = this.canvas.getBoundingClientRect();
    const ratio = window.devicePixelRatio || 1;

    this.canvas.width = rect.width * ratio;
    this.canvas.height = rect.height * ratio;

    this.ctx.setTransform(ratio, 0, 0, ratio, 0, 0);

    this.ctx.lineWidth = 2;
    this.ctx.lineCap = "round";
    this.ctx.lineJoin = "round";
    this.ctx.strokeStyle = "#111827";
  },

  start(event) {
    event.preventDefault();

    this.isDrawing = true;
    this.hasSignature = true;

    this.canvas.setPointerCapture(event.pointerId);

    const position = this.getPosition(event);

    this.ctx.beginPath();
    this.ctx.moveTo(position.x, position.y);
  },

  draw(event) {
    if (!this.isDrawing) return;

    event.preventDefault();

    const position = this.getPosition(event);

    this.ctx.lineTo(position.x, position.y);

    this.ctx.stroke();
  },

  stop() {
    this.isDrawing = false;
  },

  getPosition(event) {
    const rect = this.canvas.getBoundingClientRect();

    return {
      x: event.clientX - rect.left,
      y: event.clientY - rect.top,
    };
  },

  clear() {
    if (!this.canvas || !this.ctx) return;

    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

    this.hasSignature = false;
  },

  getData() {
    if (!this.hasSignature) {
      return null;
    }

    return this.canvas.toDataURL("image/png");
  },

  reset() {
    this.clear();
    this.resize();
  },
};
