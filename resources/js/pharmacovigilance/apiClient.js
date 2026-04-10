import axios from "axios";

const client = axios.create({
    baseURL: "/api",
    headers: {
        Accept: "application/json",
    },
});

export const tokenStore = {
    get() {
        return localStorage.getItem("pv_token");
    },
    set(token) {
        localStorage.setItem("pv_token", token);
    },
    clear() {
        localStorage.removeItem("pv_token");
    },
};

client.interceptors.request.use((config) => {
    const token = tokenStore.get();
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export async function login(payload) {
    const { data } = await client.post("/login", payload);
    return data;
}

export async function searchMedications(params) {
    const { data } = await client.get("/medications/search", { params });
    return data;
}

export async function listOrders(params) {
    const { data } = await client.get("/orders", { params });
    return data;
}

export async function getOrder(id) {
    const { data } = await client.get(`/orders/${id}`);
    return data;
}

export async function getCustomer(id) {
    const { data } = await client.get(`/customers/${id}`);
    return data;
}

export async function sendAlert(payload) {
    const { data } = await client.post("/alerts/send", payload);
    return data;
}

export async function sendBulkAlert(payload) {
    const { data } = await client.post("/alerts/send-bulk", payload);
    return data;
}

export async function exportOrdersCsv(params) {
    const response = await client.get("/orders/export/csv", {
        params,
        responseType: "blob",
    });

    const blob = new Blob([response.data], { type: "text/csv;charset=utf-8;" });
    const url = globalThis.URL.createObjectURL(blob);
    const link = document.createElement("a");
    const disposition = response.headers["content-disposition"] || "";
    const filenameMatch = disposition.match(/filename="?([^";]+)"?/i);
    const filename = filenameMatch ? filenameMatch[1] : "orders-export.csv";

    link.href = url;
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    globalThis.URL.revokeObjectURL(url);
}
