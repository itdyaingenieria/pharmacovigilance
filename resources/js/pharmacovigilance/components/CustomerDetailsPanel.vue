<script setup>
defineProps({
    customer: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <section class="pv-card" v-if="customer">
        <h3>Customer Details</h3>
        <p><strong>Name:</strong> {{ customer.name }}</p>
        <p><strong>Email:</strong> {{ customer.email }}</p>
        <p><strong>Phone:</strong> {{ customer.phone }}</p>
    </section>
</template>
