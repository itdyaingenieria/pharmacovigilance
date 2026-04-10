<script setup>
import { reactive } from 'vue';

const emit = defineEmits(['submit']);

const form = reactive({
    username: '',
    password: '',
});

function onSubmit() {
    emit('submit', { ...form });
}
</script>

<template>
    <section class="pv-card pv-login-card">
        <h1>Pharmacovigilance Access</h1>
        <p>Sign in with your account to manage lot-based safety alerts.</p>
        <form class="pv-form" @submit.prevent="onSubmit">
            <label>
                Username
                <input v-model="form.username" type="text" required autocomplete="username">
            </label>
            <label>
                Password
                <input v-model="form.password" type="password" required autocomplete="current-password">
            </label>
            <button type="submit">Sign in</button>
        </form>
    </section>
</template>
