export function debounce(callback, delay = 350) {
  let timeout;

  const debounced = (...args) => {
    window.clearTimeout(timeout);
    timeout = window.setTimeout(() => callback(...args), delay);
  };

  debounced.cancel = () => {
    window.clearTimeout(timeout);
  };

  return debounced;
}
