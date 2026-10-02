function mockDate(daysAgo) {
  const date = new Date();
  date.setHours(0, 18, 42, 0);
  date.setDate(date.getDate() - daysAgo);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day} 00:18:42`;
}

function dateOnly(value) {
  return String(value).slice(0, 10);
}

function mockPeriod(granularity, periodsBack) {
  const date = new Date();
  date.setHours(0, 0, 0, 0);

  if (granularity === "weekly") {
    date.setDate(date.getDate() - ((date.getDay() + 6) % 7) - periodsBack * 7);
  } else if (granularity === "monthly") {
    date.setDate(1);
    date.setMonth(date.getMonth() - periodsBack);
  } else {
    date.setDate(date.getDate() - periodsBack);
  }

  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

function periodOverlapsRange(period, granularity, from, to) {
  const start = new Date(`${period}T00:00:00`);
  const end = new Date(start);
  if (granularity === "weekly") end.setDate(end.getDate() + 6);
  if (granularity === "monthly") {
    end.setMonth(end.getMonth() + 1);
    end.setDate(end.getDate() - 1);
  }
  const fromDate = from ? new Date(`${from}T00:00:00`) : null;
  const toDate = to ? new Date(`${to}T00:00:00`) : null;
  return (!fromDate || end >= fromDate) && (!toDate || start <= toDate);
}

export function getMockDashboardData(params) {
  const regions = ["Region IV-A", "Region III", "NCR", "Region V"];
  const clientTypes = ["Citizen", "Business", "Government"];
  const services = [
    "Dental Service",
    "Medical Consultation",
    "Laboratory Service",
  ];
  const regionFactors = {
    "Region IV-A": 0.31,
    "Region III": 0.22,
    NCR: 0.18,
    "Region V": 0.12,
  };
  const clientFactors = { Citizen: 0.58, Business: 0.25, Government: 0.17 };
  let factor = 1;
  if (params.region) factor *= regionFactors[params.region] || 0.15;
  if (params.clientType) factor *= clientFactors[params.clientType] || 0.2;
  if (params.from || params.to) {
    const from = new Date(
      `${params.from || dateOnly(mockDate(365))}T00:00:00`,
    );
    const to = new Date(`${params.to || dateOnly(mockDate(0))}T00:00:00`);
    const days = Math.max(1, Math.floor((to - from) / 86400000) + 1);
    factor *= Math.min(1, days / 365);
  }
  const count = (value) => Math.max(0, Math.round(value * factor));
  const ratingOffset =
    (params.region === "NCR"
      ? 0.04
      : params.region === "Region V"
        ? -0.06
        : 0) +
    (params.clientType === "Business"
      ? 0.03
      : params.clientType === "Government"
        ? -0.02
        : 0);

  const responseRecords = Array.from({ length: 36 }, (_, index) => ({
    date: mockDate(index * 3),
    clientType: clientTypes[index % clientTypes.length],
    region: regions[(index * 3) % regions.length],
    service: services[index % services.length],
    average: Number(
      (4.25 + ((index * 7) % 27) / 100 + ratingOffset).toFixed(2),
    ),
    status: "Completed",
  })).filter(
    (item) =>
      (!params.region || item.region === params.region) &&
      (!params.clientType || item.clientType === params.clientType) &&
      (!params.from || dateOnly(item.date) >= params.from) &&
      (!params.to || dateOnly(item.date) <= params.to),
  );

  const comments = [
    "The service was fast and convenient.",
    "Staff were courteous and explained each step clearly.",
    "The clinic was clean and easy to find.",
    "I appreciate the short waiting time.",
    "The process was organized and straightforward.",
    "Please continue providing helpful service to students.",
    "The staff answered my questions with care.",
    "Overall, I had a very good experience.",
    "The information provided was clear and useful.",
    "Thank you for making the process easy.",
    "Service was good; adding another service window may help.",
    "The staff were professional and accommodating.",
  ];
  const suggestionRecords = comments
    .map((suggestion, index) => ({
      suggestion,
      evaluation_date: mockDate(index * 4),
      region: regions[(index * 3) % regions.length],
      clientType: clientTypes[index % clientTypes.length],
    }))
    .filter(
      (item) =>
        (!params.region || item.region === params.region) &&
        (!params.clientType || item.clientType === params.clientType) &&
        (!params.from || dateOnly(item.evaluation_date) >= params.from) &&
        (!params.to || dateOnly(item.evaluation_date) <= params.to),
    );
  const suggestionPageSize = params.allSuggestions ? 20 : 5;
  const suggestionPages = Math.ceil(
    suggestionRecords.length / suggestionPageSize,
  );
  const suggestionPage = params.allSuggestions
    ? Math.min(params.suggestionPage, Math.max(1, suggestionPages))
    : 1;
  const suggestionStart = (suggestionPage - 1) * suggestionPageSize;
  const trendLength = params.trend === "daily" ? 14 : 12;
  const trend = Array.from({ length: trendLength }, (_, index) => ({
    period: mockPeriod(params.trend, trendLength - index - 1),
    average: Number(
      (4.28 + ((index * 11) % 22) / 100 + ratingOffset).toFixed(2),
    ),
    responses: count(35 + ((index * 13) % 45)),
  })).filter((item) =>
    periodOverlapsRange(item.period, params.trend, params.from, params.to),
  );

  const charter = [
    {
      id: "CC1",
      items: [
        { label: "I know what a CC is and I saw this office's CC.", base: 726 },
        {
          label: "I know what a CC is but I did not see this office's CC.",
          base: 291,
        },
        {
          label: "I learned of the CC only when I saw this office's CC.",
          base: 168,
        },
        {
          label:
            "I do not know what a CC is and I did not see one in this office.",
          base: 63,
        },
      ],
    },
    {
      id: "CC2",
      items: [
        { label: "Easy to see", base: 745 },
        { label: "Somewhat easy to see", base: 297 },
        { label: "Difficult to see", base: 91 },
        { label: "Not visible at all", base: 44 },
        { label: "N/A", base: 71 },
      ],
    },
    {
      id: "CC3",
      items: [
        { label: "Helped very much", base: 802 },
        { label: "Somewhat helped", base: 309 },
        { label: "Did not help", base: 68 },
        { label: "N/A", base: 69 },
      ],
    },
  ].map((dimension) => {
    const items = dimension.items.map((item) => ({
      ...item,
      responses: count(item.base),
    }));
    const total = items.reduce((sum, item) => sum + item.responses, 0);
    return {
      id: dimension.id,
      items: items.map(({ label, responses }) => ({
        label,
        responses,
        percentage: total ? Number(((responses / total) * 100).toFixed(1)) : 0,
      })),
    };
  });

  return {
    summary: {
      responses: count(1248),
      overallSatisfaction: Number((4.42 + ratingOffset).toFixed(2)),
      satisfactionRate: Number(
        Math.min(99.9, 90 + ratingOffset * 10).toFixed(1),
      ),
      responsesToday: count(26),
    },
    dimensions: [4.52, 4.41, 4.36, 4.39, 4.44, 4.18, 4.46, 4.53, 4.49].map(
      (average, index) => ({
        id: `SQD${index}`,
        average: Number((average + ratingOffset).toFixed(2)),
        responses: count(index === 8 ? 1182 : 1186),
      }),
    ),
    distribution: [
      { label: "Strongly Disagree", responses: count(118) },
      { label: "Disagree", responses: count(203) },
      { label: "Neither Agree nor Disagree", responses: count(742) },
      { label: "Agree", responses: count(3624) },
      { label: "Strongly Agree", responses: count(5983) },
      { label: "Not Applicable", responses: count(562) },
    ],
    trend,
    charter,
    services: [
      {
        service: "Dental Service",
        responses: count(428),
        average: Number((4.51 + ratingOffset).toFixed(2)),
      },
      {
        service: "Medical Consultation",
        responses: count(351),
        average: Number((4.38 + ratingOffset).toFixed(2)),
      },
      {
        service: "Laboratory Service",
        responses: count(276),
        average: Number((4.46 + ratingOffset).toFixed(2)),
      },
      {
        service: "Other Health Service",
        responses: count(193),
        average: Number((4.31 + ratingOffset).toFixed(2)),
      },
    ],
    demographics: {
      clientType: [
        { label: "Citizen", responses: count(764) },
        { label: "Business", responses: count(312) },
        { label: "Government", responses: count(172) },
      ].filter(
        (item) => !params.clientType || item.label === params.clientType,
      ),
      sex: [
        { label: "Female", responses: count(738) },
        { label: "Male", responses: count(510) },
      ],
      ageGroup: [
        { label: "Below 18", responses: count(116) },
        { label: "18-24", responses: count(422) },
        { label: "25-34", responses: count(327) },
        { label: "35-44", responses: count(183) },
        { label: "45-54", responses: count(128) },
        { label: "55+", responses: count(72) },
      ],
      region: [
        { label: "Region IV-A", responses: count(566) },
        { label: "Region III", responses: count(291) },
        { label: "NCR", responses: count(232) },
        { label: "Region V", responses: count(159) },
      ].filter((item) => !params.region || item.label === params.region),
    },
    suggestions: {
      items: suggestionRecords.slice(
        suggestionStart,
        suggestionStart + suggestionPageSize,
      ),
      total: suggestionRecords.length,
      page: suggestionPage,
      pages: suggestionPages,
    },
    recent: {
      items: responseRecords.slice((params.page - 1) * 10, params.page * 10),
      page: params.page,
      pageSize: 10,
      total: responseRecords.length,
      pages: Math.ceil(responseRecords.length / 10),
    },
    options: { regions, clientTypes },
  };
}
