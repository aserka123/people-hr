<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import api, { errMsg } from '../api';
import { auth } from '../auth';

const stats = ref(null), now = ref(new Date()), msg = ref(''), err = ref(''), busy = ref(false);
let timer;
const hhmm = (d) => d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
const labels = { employees: 'Active employees', present_today: 'Present today', late_today: 'Late today', pending_leaves: 'Leave requests waiting',
  open_jobs: 'Open roles', active_applicants: 'Applicants in progress', avg_rating: 'Average review rating' };

async function load() { stats.value = (await api.get('dashboard')).data; }
async function clock() {
  busy.value = true; msg.value = err.value = '';
  try {
    const { data } = await api.post('attendance/clock');
    msg.value = data.clock_out ? `Clocked out at ${data.clock_out.slice(0, 5)} — ${data.hours} hours today.` : `Clocked in at ${data.clock_in.slice(0, 5)}${data.status === 'late' ? ' (late)' : ''}.`;
    load();
  } catch (e) { err.value = errMsg(e); } finally { busy.value = false; }
}
onMounted(() => { load(); timer = setInterval(() => (now.value = new Date()), 30000); });
onUnmounted(() => clearInterval(timer));
</script>

<template>
  <h1>Hello, {{ auth.user?.name }}</h1>
  <p class="sub">{{ now.toLocaleDateString([], { weekday: 'long', month: 'long', day: 'numeric' }) }}</p>
  <div class="clock">
    <div><div class="time">{{ hhmm(now) }}</div><div>Your attendance for today</div></div>
    <button class="btn" :disabled="busy" @click="clock">Clock in / out</button>
  </div>
  <div v-if="msg" class="msg ok">{{ msg }}</div>
  <div v-if="err" class="msg err">{{ err }}</div>
  <div v-if="stats" class="stats">
    <div v-for="(v, k) in stats" :key="k" class="stat"><b>{{ v }}</b><span>{{ labels[k] }}</span></div>
  </div>
</template>
