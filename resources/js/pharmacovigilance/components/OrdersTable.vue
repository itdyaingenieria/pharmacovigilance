<script setup>
const props = defineProps({
    rows: {
        type: Array,
        default: () => [],
    },
    selectedOrderIds: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits([
    'view-order',
    'view-customer',
    'alert',
    'toggle-order',
    'toggle-all',
    'bulk-alert',
    'export-csv',
]);

function isSelected(orderId) {
    return (Array.isArray(props.selectedOrderIds) ? props.selectedOrderIds : []).includes(orderId);
}
</script>

<template>
    <section class="pv-card">
        <div class="pv-row-between">
            <h2>Order results</h2>
            <div class="pv-actions">
                <button class="pv-link" @click="$emit('export-csv')">Export CSV</button>
                <button class="pv-link" @click="$emit('bulk-alert')" :disabled="selectedOrderIds.length === 0">Send bulk
                    alert ({{ selectedOrderIds.length }})</button>
            </div>
        </div>
        <div class="pv-table-wrap">
            <table class="pv-table">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" :checked="rows.length > 0 && selectedOrderIds.length === rows.length"
                                @change="$emit('toggle-all', $event.target.checked)">
                        </th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Purchase date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="order in rows" :key="order.id">
                        <td>
                            <input type="checkbox" :checked="isSelected(order.id)"
                                @change="$emit('toggle-order', { orderId: order.id, checked: $event.target.checked })">
                        </td>
                        <td>#{{ order.id }}</td>
                        <td>{{ order.customer?.name }}</td>
                        <td>
                            <div>{{ order.customer?.email }}</div>
                            <small>{{ order.customer?.phone }}</small>
                        </td>
                        <td>{{ order.purchase_date }}</td>
                        <td class="pv-actions">
                            <button class="pv-link" @click="$emit('view-order', order)">View order</button>
                            <button class="pv-link" @click="$emit('alert', order)">Alert Buyer</button>
                            <button class="pv-link" @click="$emit('view-customer', order.customer)">View
                                Buyer</button>
                        </td>
                    </tr>
                    <tr v-if="rows.length === 0">
                        <td colspan="6" class="pv-empty">No results found for the current filters.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
