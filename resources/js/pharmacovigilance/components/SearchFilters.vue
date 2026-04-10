<script setup>
import { reactive } from 'vue';

const emit = defineEmits(['search']);

const form = reactive({
    lot: '951357',
    start_date: '',
    end_date: '',
});

function submit() {
    emit('search', { ...form });
}
</script>

<template>
    <section class="pv-card">
        <div class="pv-row-between">
            <h2>Medication search by lot</h2>
            <span class="pv-chip">Default range: last 30 days</span>
        </div>
        <form class="pv-grid-form" @submit.prevent="submit">
            <label>
                Lot number (required)
                <input v-model="form.lot" type="text" required>
            </label>
            <label>
                Start date
                <input v-model="form.start_date" type="date">
            </label>
            <label>
                End date
                <input v-model="form.end_date" type="date">
            </label>
            <button type="submit">Search orders</button>
        </form>
    </section>
</template>
