import { dom } from "../utils/dom.js";
import { formatNumber } from "../utils/formatNumber.js";

export const chart = {
  renderActivityPeriod(activity, period) {
    const data = activity?.[period];

    if (!data) {
      console.warn(`No patient activity data found for: ${period}`);

      return;
    }

    const labels = Object.keys(data);

    const values = Object.values(data).map(Number);

    this.renderActivityChart(values, labels);

    this.renderActivityYAxis(values);

    const total = values.reduce((sum, value) => sum + value, 0);

    dom.setNumber("activityTotal", total);

    this.updateActivityLabel(period);
  },

  initActivityFilter(activity) {
    const buttons = document.querySelectorAll(".chart-filter button");

    if (!buttons.length) {
      return;
    }

    buttons.forEach((button) => {
      button.addEventListener("click", () => {
        const period = button.dataset.period;

        if (!period) {
          return;
        }

        // Update active button
        buttons.forEach((item) => {
          item.classList.remove("active");
        });

        button.classList.add("active");

        // Render selected period
        this.renderActivityPeriod(activity, period);
      });
    });
  },

  renderActivityChart(values, labels) {
    const svg = dom.get("patientActivityChart");

    const daysContainer = dom.get("chartDays");

    if (!svg || !daysContainer) {
      return;
    }

    const width = 700;
    const height = 260;

    const paddingTop = 20;
    const paddingBottom = 20;

    const maxValue = Math.max(...values, 1);

    const points = values.map((value, index) => {
      const x =
        values.length === 1 ? width / 2 : (index / (values.length - 1)) * width;

      const y =
        height -
        paddingBottom -
        (value / maxValue) * (height - paddingTop - paddingBottom);

      return {
        x,
        y,
      };
    });

    const linePath = points
      .map((point, index) => {
        return `${index === 0 ? "M" : "L"}${point.x},${point.y}`;
      })
      .join(" ");

    const fillPath = `
        ${linePath}
        L${width},${height}
        L0,${height}
        Z
    `;

    const line = svg.querySelector(".chart-line");

    const fill = svg.querySelector(".chart-fill");

    if (line) {
      line.setAttribute("d", linePath);
    }

    if (fill) {
      fill.setAttribute("d", fillPath);
    }

    // Update labels
    daysContainer.innerHTML = labels
      .map((label) => `<span>${label}</span>`)
      .join("");
  },

  renderActivityYAxis(values) {
    const container = dom.get("chartYAxis");

    if (!container) {
      return;
    }

    const maxValue = Math.max(...values, 1);

    const tickCount = 5;

    const step = this.calculateYAxisStep(maxValue, tickCount);

    const maximum = step * tickCount;

    const ticks = [];

    for (let i = tickCount; i >= 0; i--) {
      ticks.push(maximum - step * (tickCount - i));
    }

    container.innerHTML = ticks
      .map((value) => `<span>${formatNumber(value)}</span>`)
      .join("");
  },

  calculateYAxisStep(maxValue, tickCount) {
    if (maxValue <= 0) {
      return 1;
    }

    const roughStep = maxValue / tickCount;

    const magnitude = Math.pow(10, Math.floor(Math.log10(roughStep)));

    const normalized = roughStep / magnitude;

    let niceStep;

    if (normalized <= 1) {
      niceStep = 1;
    } else if (normalized <= 2) {
      niceStep = 2;
    } else if (normalized <= 5) {
      niceStep = 5;
    } else {
      niceStep = 10;
    }

    return niceStep * magnitude;
  },

  updateActivityLabel(period) {
    const labels = {
      weekly: "total this week",
      monthly: "total this year",
      yearly: "total over 5 years",
    };

    dom.setText("activityLabel", labels[period] ?? "");
  },
};
