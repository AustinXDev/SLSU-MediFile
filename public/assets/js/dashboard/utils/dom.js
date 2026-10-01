export const dom = {
  get(id) {
    return document.getElementById(id);
  },

  setText(id, value) {
    const element = this.get(id);

    if (element) element.innerText = value || "";
  },

  setNumber(id, value) {
    const element = this.get(id);

    if (element) element.innerText = value || 0;
  },

  setChange(id, change, direction) {
    const element = this.get(id);

    if (!element) return;

    const valueElement = element.querySelector(".stat-change-value");

    if (!valueElement) return;

    const percentage = Math.abs(Number(change) || 0);

    let arrow = "→";
    let className = "neutral";

    if (direction === "up") {
      arrow = "↑";
      className = "positive";
    } else if (direction === "down") {
      arrow = "↓";
      className = "negative";
    }

    valueElement.textContent = `${arrow} ${percentage}%`;

    valueElement.classList.remove("positive", "negative", "neutral");

    valueElement.classList.add(className);
  },
};
