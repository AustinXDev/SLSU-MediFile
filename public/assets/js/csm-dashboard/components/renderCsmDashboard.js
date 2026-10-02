import { getData, USE_MOCK_DATA } from "./dashboardData.js";

const state = {
  trend: "daily",
  page: 1,
  allSuggestions: false,
  suggestionPage: 1,
};

const sqdQuestions = [
  "I am satisfied with the service that I availed.",
  "I spent a reasonable amount of time for my transaction.",
  "The office followed the transaction's requirements and steps based on the information provided.",
  "The steps (including payment) I needed to do for my transaction were easy and simple.",
  "I easily found information about my transaction from the office or its website.",
  "I paid a reasonable amount of fees for my transaction. (If the service was free, mark N/A.)",
  "I feel the office was fair to everyone, or 'walang palakasan', during my transaction.",
  "I was treated courteously by the staff, and (if asked for help) the staff was helpful.",
  "I got what I needed from the government office, or (if denied) denial of request was sufficiently explained to me.",
];

function makeElement(tag, className, text) {
  const element = document.createElement(tag);
  if (className) element.className = className;
  if (text !== undefined && text !== null) element.textContent = text;
  return element;
}

function setStatus(message = "", isError = false) {
  const statusMessage = document.querySelector("#csmStatus");
  statusMessage.textContent = message;
  statusMessage.classList.toggle("is-error", isError);
}

function formatNumber(value) {
  return new Intl.NumberFormat().format(Number(value) || 0);
}

function formatDate(
  value,
  options = { year: "numeric", month: "long", day: "numeric" },
) {
  const dateValue = getDateValue(value);
  if (!dateValue) return "Not specified";
  const parsed = new Date(`${dateValue}T00:00:00`);
  return new Intl.DateTimeFormat(undefined, options).format(parsed);
}

function getDateValue(value) {
  const dateValue = String(value ?? "")
    .trim()
    .slice(0, 10);
  if (!/^\d{4}-\d{2}-\d{2}$/.test(dateValue) || dateValue === "0000-00-00") {
    return null;
  }

  const parsed = new Date(`${dateValue}T00:00:00`);
  const [year, month, day] = dateValue.split("-").map(Number);
  if (
    Number.isNaN(parsed.getTime()) ||
    parsed.getFullYear() !== year ||
    parsed.getMonth() + 1 !== month ||
    parsed.getDate() !== day
  ) {
    return null;
  }

  return dateValue;
}

function updateOptions(select, values, defaultText, selectedValue) {
  select.replaceChildren(new Option(defaultText, ""));
  values.forEach((value) => select.add(new Option(value, value)));
  select.value = values.includes(selectedValue) ? selectedValue : "";
}

function renderSummary(summary) {
  document.querySelector("#totalResponses").textContent = formatNumber(
    summary.responses,
  );
  document.querySelector("#overallSatisfaction").textContent =
    summary.overallSatisfaction === null
      ? "--"
      : `${Number(summary.overallSatisfaction).toFixed(2)} / 5`;
  document.querySelector("#satisfactionRate").textContent =
    summary.satisfactionRate === null
      ? "--"
      : `${Number(summary.satisfactionRate).toFixed(1)}%`;
  document.querySelector("#responsesToday").textContent = formatNumber(
    summary.responsesToday,
  );
}

function renderDimensions(dimensions) {
  const container = document.querySelector("#sqdDimensions");
  container.replaceChildren();
  dimensions.forEach((dimension) => {
    const row = makeElement("div", "csm-dimension");
    const label = makeElement("div", "csm-dimension-label");
    label.append(makeElement("strong", "", dimension.id));
    label.append(
      makeElement("span", "", sqdQuestions[Number(dimension.id.slice(3))]),
    );
    const track = makeElement("div", "csm-bar-track");
    const fill = makeElement("span", "csm-bar-fill");
    fill.style.setProperty(
      "--bar-width",
      `${Math.max(0, Math.min(100, (Number(dimension.average) / 5) * 100 || 0))}%`,
    );
    track.append(fill);
    row.append(label, track);
    row.append(
      makeElement(
        "strong",
        "csm-dimension-average",
        dimension.average === null
          ? "-- / 5"
          : `${Number(dimension.average).toFixed(2)} / 5`,
      ),
    );
    row.append(
      makeElement(
        "span",
        "csm-dimension-count",
        `${formatNumber(dimension.responses)} responses`,
      ),
    );
    container.append(row);
  });
}

function renderBarList(
  container,
  items,
  valueKey = "responses",
  percentageKey = null,
) {
  container.replaceChildren();
  if (!items.length) {
    container.append(
      makeElement("p", "csm-empty", "No results for the selected filters."),
    );
    return;
  }
  const maxValue = Math.max(
    1,
    ...items.map(
      (item) =>
        Number(percentageKey ? item[percentageKey] : item[valueKey]) || 0,
    ),
  );
  items.forEach((item) => {
    const value = Number(item[valueKey]) || 0;
    const chartValue = Number(percentageKey ? item[percentageKey] : value) || 0;
    const row = makeElement("div", "csm-bar-row");
    const heading = makeElement("div", "csm-bar-heading");
    heading.append(makeElement("span", "", item.label));
    heading.append(
      makeElement(
        "span",
        "",
        percentageKey
          ? `${formatNumber(value)} · ${Number(item[percentageKey]).toFixed(1)}%`
          : formatNumber(value),
      ),
    );
    const track = makeElement("div", "csm-bar-track");
    const fill = makeElement("span", "csm-bar-fill");
    fill.style.setProperty("--bar-width", `${(chartValue / maxValue) * 100}%`);
    track.append(fill);
    row.append(heading, track);
    container.append(row);
  });
}

function renderDistribution(items) {
  const total = items.reduce((sum, item) => sum + Number(item.responses), 0);
  renderBarList(
    document.querySelector("#ratingDistribution"),
    items.map((item) => ({
      label: item.label,
      responses: item.responses,
      percentage: total ? (Number(item.responses) / total) * 100 : 0,
    })),
    "responses",
    "percentage",
  );
}

function renderTrend(items) {
  const container = document.querySelector("#satisfactionTrend");
  container.replaceChildren();
  if (!items.length) {
    container.append(
      makeElement("p", "csm-empty", "No results for the selected filters."),
    );
    return;
  }
  const labelOptions =
    state.trend === "monthly"
      ? { year: "2-digit", month: "short" }
      : { month: "short", day: "numeric" };
  items.forEach((item) => {
    const point = makeElement("div", "csm-trend-point");
    point.title = `${formatDate(item.period)}: ${Number(item.average).toFixed(2)} / 5 (${formatNumber(item.responses)} responses)`;
    point.append(
      makeElement("span", "csm-trend-value", Number(item.average).toFixed(2)),
    );
    const column = makeElement("div", "csm-trend-column");
    const bar = makeElement("span");
    bar.style.setProperty(
      "--bar-height",
      `${Math.max(0, Math.min(100, (Number(item.average) / 5) * 100))}%`,
    );
    column.append(bar);
    point.append(column);
    point.append(
      makeElement(
        "time",
        "csm-trend-label",
        formatDate(item.period, labelOptions),
      ),
    );
    container.append(point);
  });
}

function renderCharter(items) {
  items.forEach((dimension) => {
    const section = document.querySelector(
      `[data-charter="${dimension.id}"] .csm-bar-list`,
    );
    renderBarList(section, dimension.items, "responses", "percentage");
  });
}

function renderServices(items) {
  const body = document.querySelector("#serviceResults");
  body.replaceChildren();
  if (!items.length) {
    const row = makeElement("tr");
    const cell = makeElement(
      "td",
      "csm-empty",
      "No service results for the selected filters.",
    );
    cell.colSpan = 3;
    row.append(cell);
    body.append(row);
    return;
  }
  items.forEach((item) => {
    const row = makeElement("tr");
    row.append(makeElement("td", "", item.service || "Not specified"));
    row.append(makeElement("td", "", formatNumber(item.responses)));
    row.append(
      makeElement("td", "csm-rating", `${Number(item.average).toFixed(2)} / 5`),
    );
    body.append(row);
  });
}

function renderDemographics(demographics) {
  renderBarList(
    document.querySelector("#demographicClientType"),
    demographics.clientType,
  );
  renderBarList(document.querySelector("#demographicSex"), demographics.sex);
  renderBarList(
    document.querySelector("#demographicAge"),
    demographics.ageGroup,
  );
  renderBarList(
    document.querySelector("#demographicRegion"),
    demographics.region,
  );
}

function addPaginationButtons(
  container,
  currentPage,
  totalPages,
  onPageChange,
) {
  const addButton = (label, page, disabled = false, active = false) => {
    const button = makeElement("button", active ? "active" : "", label);
    button.type = "button";
    button.disabled = disabled;
    if (active) button.setAttribute("aria-current", "page");
    button.addEventListener("click", () => onPageChange(page));
    container.append(button);
  };
  addButton("Previous", currentPage - 1, currentPage <= 1);
  const start = Math.max(1, currentPage - 2);
  const end = Math.min(totalPages, currentPage + 2);
  for (let page = start; page <= end; page++) {
    addButton(String(page), page, false, page === currentPage);
  }
  addButton("Next", currentPage + 1, currentPage >= totalPages);
}

function renderSuggestions(suggestions, onPageChange) {
  const container = document.querySelector("#clientSuggestions");
  container.replaceChildren();
  if (!suggestions.items.length) {
    container.append(
      makeElement("p", "csm-empty", "No suggestions for the selected filters."),
    );
  } else {
    suggestions.items.forEach((item) => {
      const entry = makeElement("article", "csm-suggestion");
      entry.append(makeElement("p", "", item.suggestion));
      const date = makeElement("time", "", formatDate(item.evaluation_date));
      const dateValue = getDateValue(item.evaluation_date);
      if (dateValue) date.dateTime = dateValue;
      entry.append(date);
      container.append(entry);
    });
  }
  const toggle = document.querySelector("#toggleSuggestions");
  toggle.textContent = state.allSuggestions
    ? "Show Recent Suggestions"
    : "View All Suggestions";
  toggle.hidden = !state.allSuggestions && suggestions.total <= 5;

  const pagination = document.querySelector("#suggestionPagination");
  pagination.replaceChildren();
  if (state.allSuggestions && suggestions.pages > 1) {
    addPaginationButtons(
      pagination,
      suggestions.page,
      suggestions.pages,
      onPageChange,
    );
  }
}

function renderRecent(recent, onPageChange) {
  const body = document.querySelector("#recentResponses");
  body.replaceChildren();
  if (!recent.items.length) {
    const row = makeElement("tr");
    const cell = makeElement(
      "td",
      "csm-empty",
      "No responses for the selected filters.",
    );
    cell.colSpan = 6;
    row.append(cell);
    body.append(row);
  }
  recent.items.forEach((item) => {
    const row = makeElement("tr");
    row.append(makeElement("td", "", formatDate(item.date)));
    row.append(makeElement("td", "", item.clientType || "Not specified"));
    row.append(makeElement("td", "", item.region || "Not specified"));
    row.append(makeElement("td", "", item.service || "Not specified"));
    row.append(
      makeElement(
        "td",
        "csm-rating",
        item.average === null ? "--" : `${Number(item.average).toFixed(2)} / 5`,
      ),
    );
    const status = makeElement("span", "csm-status-badge", item.status);
    const statusCell = makeElement("td");
    statusCell.append(status);
    row.append(statusCell);
    body.append(row);
  });
  document.querySelector("#recentSummary").textContent =
    `${formatNumber(recent.total)} matching completed evaluations`;

  const pagination = document.querySelector("#recentPagination");
  pagination.replaceChildren();
  if (recent.pages > 1) {
    addPaginationButtons(pagination, recent.page, recent.pages, onPageChange);
  }
}

function getCurrentParams() {
  return {
    from: document.querySelector("#dateFrom").value,
    to: document.querySelector("#dateTo").value,
    region: document.querySelector("#regionFilter").value,
    clientType: document.querySelector("#clientTypeFilter").value,
    trend: state.trend,
    page: state.page,
    allSuggestions: state.allSuggestions ? 1 : 0,
    suggestionPage: state.suggestionPage,
  };
}

export const dashboard = {
  init() {
    const filtersForm = document.querySelector("#csmFilters");
    filtersForm.addEventListener("submit", (event) => {
      event.preventDefault();
      state.page = 1;
      state.allSuggestions = false;
      state.suggestionPage = 1;
      this.refresh();
    });

    document.querySelector("#resetFilters").addEventListener("click", () => {
      filtersForm.reset();
      state.page = 1;
      state.allSuggestions = false;
      state.suggestionPage = 1;
      this.refresh();
    });

    document.querySelectorAll("[data-trend]").forEach((button) => {
      button.addEventListener("click", () => {
        state.trend = button.dataset.trend;
        state.page = 1;
        document.querySelectorAll("[data-trend]").forEach((item) => {
          item.setAttribute("aria-pressed", String(item === button));
        });
        this.refresh();
      });
    });

    document
      .querySelector("#toggleSuggestions")
      .addEventListener("click", () => {
        state.allSuggestions = !state.allSuggestions;
        state.suggestionPage = 1;
        this.refresh();
      });

    this.refresh();
    if (!USE_MOCK_DATA) {
      window.setInterval(() => {
        if (!document.hidden) this.refresh();
      }, 60000);
    }
  },

  async refresh() {
    setStatus(
      USE_MOCK_DATA ? "Loading design preview..." : "Updating results...",
    );
    try {
      const data = await getData(getCurrentParams());
      this.render(data);
      setStatus(
        USE_MOCK_DATA
          ? "Design preview using illustrative mock data. Live CSM data is not being loaded."
          : `Results updated ${new Intl.DateTimeFormat(undefined, { hour: "numeric", minute: "2-digit" }).format(new Date())}.`,
      );
    } catch (error) {
      setStatus(
        error.response?.data?.message ||
          error.message ||
          "Unable to load CSM results.",
        true,
      );
    }
  },

  render(data) {
    renderSummary(data.summary);
    renderDimensions(data.dimensions);
    renderDistribution(data.distribution);
    renderTrend(data.trend);
    renderCharter(data.charter);
    renderServices(data.services);
    renderDemographics(data.demographics);
    renderSuggestions(data.suggestions, (page) => {
      state.suggestionPage = page;
      this.refresh();
    });
    renderRecent(data.recent, (page) => {
      state.page = page;
      this.refresh();
    });
    const params = getCurrentParams();
    console.log("PARAMS AFTER RESPONSE:", params);
    console.log("REGION OPTIONS:", data.options.regions);
    console.log("CLIENT OPTIONS:", data.options.clientTypes);
    updateOptions(
      document.querySelector("#regionFilter"),
      data.options.regions,
      "All Regions",
      params.region,
    );
    updateOptions(
      document.querySelector("#clientTypeFilter"),
      data.options.clientTypes,
      "All Clients",
      params.clientType,
    );
  },
};
