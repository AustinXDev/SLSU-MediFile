export const dom = {
  get(id) {
    return document.getElementById(id);
  },

  value(id) {
    return this.get(id)?.value.trim() || "";
  },

  setValue(id, value) {
    const element = this.get(id);
    if (element) element.value = value || "";
  },
};

export const selected = {
  get(query) {
    return document.querySelectorAll(query);
  },

  array(query) {
    const selectedQuery = this.get(query);
    const selectedHistory = Array.from(selectedQuery).map(
      (checkbox) => checkbox.value,
    );

    return selectedHistory;
  },

  arrayValue(query) {
    const selectedQuery = this.get(query);
    const teethData = {};

    selectedQuery.forEach((input) => {
      const val = input.value.trim();

      if (val !== "") {
        teethData[input.id] = val;
      }
    });

    return teethData;
  },
};
