export const dom = {
  get(id) {
    return document.getElementById(id);
  },

  all(selector) {
    return [...document.querySelectorAll(selector)];
  },
};
