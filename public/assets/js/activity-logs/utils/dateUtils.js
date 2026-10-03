export const dateUtils = {
  format(value, options = {}) {
    if (!value) return "—";

    const date = new Date(String(value).replace(" ", "T"));
    if (Number.isNaN(date.getTime())) return "—";

    return date.toLocaleString(undefined, {
      year: "numeric",
      month: "short",
      day: "numeric",
      hour: "numeric",
      minute: "2-digit",
      ...options,
    });
  },

  localDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  },

  filters(range, dateFrom, dateTo) {
    const today = new Date();
    const from = new Date(today);

    if (range === "today") {
      return {
        dateFrom: this.localDate(today),
        dateTo: this.localDate(today),
      };
    }

    if (range === "yesterday") {
      from.setDate(from.getDate() - 1);
      return {
        dateFrom: this.localDate(from),
        dateTo: this.localDate(from),
      };
    }

    if (range === "7" || range === "30") {
      from.setDate(from.getDate() - (Number(range) - 1));
      return {
        dateFrom: this.localDate(from),
        dateTo: this.localDate(today),
      };
    }

    if (range === "custom") {
      return { dateFrom, dateTo };
    }

    return { dateFrom: "", dateTo: "" };
  },
};
