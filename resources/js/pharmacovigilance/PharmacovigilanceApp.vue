<script setup>
import { computed, ref } from 'vue';
import {
    exportOrdersCsv,
    getCustomer,
    getOrder,
    listOrders,
    login,
    searchMedications,
    sendAlert,
    sendBulkAlert,
    tokenStore,
} from './apiClient';
import AlertModal from './components/AlertModal.vue';
import CustomerDetailsPanel from './components/CustomerDetailsPanel.vue';
import LoginPanel from './components/LoginPanel.vue';
import OrderDetailsPanel from './components/OrderDetailsPanel.vue';
import OrdersTable from './components/OrdersTable.vue';
import SearchFilters from './components/SearchFilters.vue';

const isAuthenticated = ref(Boolean(tokenStore.get()));
const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const successToast = ref('');
const warningToast = ref('');
const activeLot = ref('951357');
let successToastTimer = null;
let warningToastTimer = null;

const medications = ref([]);
const orders = ref([]);
const selectedOrder = ref(null);
const selectedCustomer = ref(null);
const pendingAlertOrder = ref(null);
const showAlertModal = ref(false);
const selectedOrderIds = ref([]);
const lastFilters = ref({ lot: '951357', start_date: '', end_date: '' });

const hasData = computed(() => orders.value.length > 0);

function clearMessages() {
    errorMessage.value = '';
    successMessage.value = '';
}

function showWarningToast(message) {
    warningToast.value = message;

    if (warningToastTimer) {
        clearTimeout(warningToastTimer);
    }

    warningToastTimer = setTimeout(() => {
        warningToast.value = '';
        warningToastTimer = null;
    }, 4500);
}

function showSuccessToast(message) {
    successToast.value = message;

    if (successToastTimer) {
        clearTimeout(successToastTimer);
    }

    successToastTimer = setTimeout(() => {
        successToast.value = '';
        successToastTimer = null;
    }, 3500);
}

function parseError(error, fallback = 'An unexpected error occurred.') {
    return error?.response?.data?.message ?? fallback;
}

async function handleLogin(credentials) {
    clearMessages();
    loading.value = true;
    try {
        const response = await login(credentials);
        tokenStore.set(response.data.access_token);
        isAuthenticated.value = true;
        successMessage.value = 'Authentication successful. You can now search lots and manage alerts.';
    } catch (error) {
        errorMessage.value = parseError(error, 'Unable to sign in.');
    } finally {
        loading.value = false;
    }
}

async function handleSearch(filters) {
    clearMessages();
    loading.value = true;
    selectedOrder.value = null;
    selectedCustomer.value = null;
    selectedOrderIds.value = [];
    activeLot.value = filters.lot;
    lastFilters.value = { ...filters };

    try {
        const [medicationsResponse, ordersResponse] = await Promise.all([
            searchMedications(filters),
            listOrders(filters),
        ]);

        medications.value = medicationsResponse.data.items ?? [];
        orders.value = ordersResponse.data.items ?? [];

        if (orders.value.length === 0) {
            successMessage.value = 'Search completed with no results for the selected lot and date range.';
        } else {
            successMessage.value = 'Search completed successfully.';
        }
    } catch (error) {
        errorMessage.value = parseError(error, 'Unable to run the search.');
    } finally {
        loading.value = false;
    }
}

function toggleOrderSelection({ orderId, checked }) {
    if (checked) {
        if (!selectedOrderIds.value.includes(orderId)) {
            selectedOrderIds.value = [...selectedOrderIds.value, orderId];
        }
        return;
    }

    selectedOrderIds.value = selectedOrderIds.value.filter((id) => id !== orderId);
}

function toggleAllOrders(checked) {
    selectedOrderIds.value = checked ? orders.value.map((order) => order.id) : [];
}

async function sendBulkAlerts() {
    if (selectedOrderIds.value.length === 0) {
        return;
    }

    clearMessages();
    loading.value = true;

    try {
        const response = await sendBulkAlert({
            order_ids: selectedOrderIds.value,
            lot_number: activeLot.value,
        });

        const summary = response.data.summary;
        successMessage.value = `Bulk alert completed. Sent: ${summary.sent}, Duplicates: ${summary.skipped_duplicate}, Failed: ${summary.failed}.`;

        if (summary.sent > 0) {
            showSuccessToast(`Bulk send successful: ${summary.sent} alert(s) sent.`);
        }

        if (summary.skipped_duplicate > 0) {
            showWarningToast(`Skipped duplicates: ${summary.skipped_duplicate} order(s) already had an alert for this lot.`);
        }
    } catch (error) {
        errorMessage.value = parseError(error, 'Unable to send bulk alerts.');
    } finally {
        loading.value = false;
    }
}

async function exportCurrentResultsCsv() {
    clearMessages();
    loading.value = true;

    try {
        await exportOrdersCsv(lastFilters.value);
        successMessage.value = 'CSV export generated successfully.';
    } catch (error) {
        errorMessage.value = parseError(error, 'Unable to export CSV.');
    } finally {
        loading.value = false;
    }
}

async function openOrderDetails(order) {
    clearMessages();
    loading.value = true;
    try {
        const response = await getOrder(order.id);
        selectedOrder.value = response.data;
    } catch (error) {
        errorMessage.value = parseError(error, 'Unable to load order details.');
    } finally {
        loading.value = false;
    }
}

async function openCustomerDetails(customer) {
    if (!customer?.id) {
        return;
    }

    clearMessages();
    loading.value = true;
    try {
        const response = await getCustomer(customer.id);
        selectedCustomer.value = response.data;
    } catch (error) {
        errorMessage.value = parseError(error, 'Unable to load customer details.');
    } finally {
        loading.value = false;
    }
}

function requestAlert(order) {
    pendingAlertOrder.value = order;
    showAlertModal.value = true;
}

function closeAlertModal() {
    showAlertModal.value = false;
    pendingAlertOrder.value = null;
}

async function confirmAlert() {
    if (!pendingAlertOrder.value) {
        return;
    }

    clearMessages();
    loading.value = true;
    try {
        await sendAlert({
            order_id: pendingAlertOrder.value.id,
            lot_number: activeLot.value,
        });
        successMessage.value = `Alert sent for order #${pendingAlertOrder.value.id}.`;
        showSuccessToast(`Alert sent successfully for order #${pendingAlertOrder.value.id}.`);
    } catch (error) {
        if (error?.response?.status === 409) {
            showWarningToast(parseError(error, 'Alert skipped: an alert was already sent for this order and lot.'));
        } else {
            errorMessage.value = parseError(error, 'Unable to send the alert.');
        }
    } finally {
        loading.value = false;
        closeAlertModal();
    }
}

function logout() {
    tokenStore.clear();
    isAuthenticated.value = false;
    medications.value = [];
    orders.value = [];
    selectedOrder.value = null;
    selectedCustomer.value = null;
    pendingAlertOrder.value = null;
    selectedOrderIds.value = [];
    clearMessages();
}
</script>

<template>
    <main class="pv-shell">
        <div class="pv-backdrop"></div>
        <section class="pv-layout">
            <header class="pv-header">
                <div>
                    <p class="pv-kicker">Safety Operations</p>
                    <h1>Pharmacovigilance Alert System</h1>
                </div>
                <button v-if="isAuthenticated" class="pv-link" @click="logout">Sign out</button>
            </header>

            <div class="pv-alert pv-alert-error" v-if="errorMessage">{{ errorMessage }}</div>
            <div class="pv-alert pv-alert-success" v-if="successMessage">{{ successMessage }}</div>

            <div v-if="!isAuthenticated">
                <LoginPanel @submit="handleLogin" />
            </div>

            <div v-else class="pv-content">
                <SearchFilters @search="handleSearch" />

                <section class="pv-card" v-if="medications.length > 0">
                    <h2>Medications linked to lot {{ activeLot }}</h2>
                    <ul class="pv-list">
                        <li v-for="medication in medications" :key="medication.id">
                            {{ medication.name }} - Lot {{ medication.lot_number }}
                        </li>
                    </ul>
                </section>

                <OrdersTable :rows="orders" @view-order="openOrderDetails" @view-customer="openCustomerDetails"
                    :selected-order-ids="selectedOrderIds" @toggle-order="toggleOrderSelection"
                    @toggle-all="toggleAllOrders" @bulk-alert="sendBulkAlerts" @export-csv="exportCurrentResultsCsv"
                    @alert="requestAlert" />

                <section class="pv-grid-two" v-if="hasData || selectedOrder || selectedCustomer">
                    <OrderDetailsPanel :order="selectedOrder" />
                    <CustomerDetailsPanel :customer="selectedCustomer" />
                </section>
            </div>
        </section>

        <AlertModal :visible="showAlertModal" :order="pendingAlertOrder" :lot="activeLot" @close="closeAlertModal"
            @confirm="confirmAlert" />

        <div class="pv-toast pv-toast-success" v-if="successToast">{{ successToast }}</div>
        <div class="pv-toast pv-toast-warning" v-if="warningToast">{{ warningToast }}</div>

        <div class="pv-loading" v-if="loading">Processing...</div>
    </main>
</template>
