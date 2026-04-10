<script setup>
defineProps({
    order: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <section class="pv-card" v-if="order">
        <h3>Order Details #{{ order.id }}</h3>
        <p><strong>Date:</strong> {{ order.purchase_date }}</p>
        <ul class="pv-list">
            <li v-for="med in order.medications" :key="`${order.id}-${med.id}`">
                {{ med.name }} | Lot: {{ med.lot_number }} | Quantity: {{ med.quantity }}
            </li>
        </ul>
    </section>
</template>
