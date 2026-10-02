import { dom } from "./dom.js";
import { fetchJson, getUrl } from "./api.js";
import { state, setLoading } from "./state.js";
import {
  notify,
  populateFilterData,
  renderTable,
  renderPagination,
} from "./userTable.js";

export const userData = {
  async loadAccounts() {
    if (state.isLoading) {
      return;
    }

    const requestId = ++state.loadRequestId;
    setLoading(true);

    try {
      const url = new URL(getUrl("users/index.php"));
      url.searchParams.set("page", String(state.page));
      url.searchParams.set("limit", String(state.pageSize));
      if (state.search) url.searchParams.set("search", state.search);
      if (state.role) url.searchParams.set("role", state.role);
      if (state.status) url.searchParams.set("status", state.status);

      const response = await fetchJson(url.toString());
      if (requestId !== state.loadRequestId) {
        return;
      }

      const payload =
        response && typeof response === "object" && "data" in response
          ? (response.data ?? {})
          : (response ?? {});

      const accounts = Array.isArray(payload.accounts)
        ? payload.accounts
        : Array.isArray(response?.accounts)
          ? response.accounts
          : [];

      const pagination =
        payload.pagination && typeof payload.pagination === "object"
          ? payload.pagination
          : { page: 1, totalPages: 1, total: 0 };

      if (payload.roles && payload.statuses) {
        populateFilterData(payload);
      }

      renderTable(accounts);
      renderPagination(pagination);

      if (dom.resultSummary) {
        dom.resultSummary.textContent = `Showing ${pagination.total || 0} account${pagination.total === 1 ? "" : "s"}`;
      }

      return payload;
    } catch (error) {
      if (requestId !== state.loadRequestId) {
        return null;
      }

      notify(error.message, "error");
      renderTable([]);
      renderPagination({ page: 1, totalPages: 1, total: 0 });
      if (dom.resultSummary) {
        dom.resultSummary.textContent = "Unable to load accounts";
      }
      return null;
    } finally {
      if (requestId === state.loadRequestId) {
        setLoading(false);
      }
    }
  },

  async getAccount(id) {
    const response = await fetchJson(`${getUrl("users/index.php")}?id=${id}`);
    return response.data || response.account || null;
  },

  async createAccount(payload) {
    return fetchJson(getUrl("users/index.php"), {
      method: "POST",
      body: payload,
    });
  },

  async updateAccount(payload) {
    return fetchJson(getUrl("users/index.php"), {
      method: "PUT",
      body: payload,
    });
  },

  async resetPassword(id, password) {
    return fetchJson(getUrl("users/index.php"), {
      method: "POST",
      body: {
        action: "reset-password",
        id,
        password,
      },
    });
  },

  async deleteAccount(id) {
    return fetchJson(getUrl("users/index.php"), {
      method: "DELETE",
      body: { id },
    });
  },
};
