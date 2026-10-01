import { dom } from "../../patients/utils/dom.js";

export const progress = {
  calculatePercentage(value, total) {
    if (!total) {
      return 0;
    }

    return Math.round((value / total) * 100);
  },

  setProgress(id, percentage) {
    const element = dom.get(id);

    if (!element) {
      return;
    }

    element.style.width = `${percentage}%`;
    this.setColor(element, percentage);
  },

  setColor(element, percentage) {
    if (!element) return;

    if (percentage >= 70) {
      element.style.backgroundColor = "#10b981"; // green
    } else if (percentage >= 40) {
      element.style.backgroundColor = "#f59e0b"; // amber
    } else {
      element.style.backgroundColor = "#ef4444"; // red
    }
  },
};
