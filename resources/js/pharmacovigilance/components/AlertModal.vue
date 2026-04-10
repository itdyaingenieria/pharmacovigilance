<script setup>
defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    order: {
        type: Object,
        default: null,
    },
    lot: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['close', 'confirm']);
</script>

<template>
    <div class="pv-modal" v-if="visible">
        <div class="pv-modal-card">
            <div class="pv-modal-header">
                <h3 class="pv-modal-title">Send Alert to Customer</h3>
                <button class="pv-close" type="button" aria-label="Close" @click="$emit('close')">x</button>
            </div>
            <p class="pv-modal-warning">
                <strong>Warning:</strong> Important recall notice for medication Lot #{{ lot || '951357' }}.
                Please contact the pharmacy immediately.
            </p>
            <div class="pv-modal-actions">
                <button @click="$emit('confirm')">Send Email</button>
                <button class="pv-link" @click="$emit('close')">Cancel</button>
            </div>
        </div>
    </div>
</template>
