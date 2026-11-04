<script setup>
import { useRouter } from 'vue-router';
import { auth } from './auth';
import api from './api';
import { modules } from './modules';

const router = useRouter();
const links = () => modules.filter((m) => !m.hidden && (auth.isHr || !m.hrOnly));
async function logout() {
  try { await api.post('logout'); } catch {}
  auth.clear(); router.push('/login');
}
</script>

<template>
  <div v-if="auth.token" class="shell">
    <nav class="side">
      <div class="brand">People HR</div>
      <router-link to="/">Dashboard</router-link>
      <router-link v-for="m in links()" :key="m.key" :to="`/m/${m.key}`">{{ m.title }}</router-link>
      <router-link v-if="auth.isHr" to="/m/leave-types">Leave types</router-link>
      <div class="who">{{ auth.user?.name }}<br>{{ auth.user?.role }}<br><button class="btn sm" @click="logout">Sign out</button></div>
    </nav>
    <main><router-view :key="$route.fullPath" /></main>
  </div>
  <router-view v-else />
</template>
