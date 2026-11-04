<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api, { errMsg } from '../api';
import { auth } from '../auth';

const router = useRouter();
const email = ref('admin@example.com'), password = ref(''), error = ref(''), busy = ref(false);
async function submit() {
  busy.value = true; error.value = '';
  try { const { data } = await api.post('login', { email: email.value, password: password.value }); auth.set(data.token, data.user); router.push('/'); }
  catch (e) { error.value = errMsg(e); } finally { busy.value = false; }
}
</script>

<template>
  <div class="login">
    <form @submit.prevent="submit">
      <h1>Sign in to People HR</h1>
      <p class="sub">Use your work email.</p>
      <div v-if="error" class="msg err">{{ error }}</div>
      <div class="field"><label for="e">Email</label><input id="e" type="email" v-model="email" required autocomplete="username"></div>
      <div class="field"><label for="p">Password</label><input id="p" type="password" v-model="password" required autocomplete="current-password"></div>
      <button class="btn primary" style="width:100%" :disabled="busy">{{ busy ? 'Signing in…' : 'Sign in' }}</button>
    </form>
  </div>
</template>
