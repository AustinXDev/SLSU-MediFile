const api = axios.create({
  baseURL: window.APP_ENV?.APP_URL || "http://localhost/SLSU-MediFile/api/",
  timeout: 10000,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

export default api;
