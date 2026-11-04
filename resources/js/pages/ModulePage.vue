<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api, { errMsg } from '../api';
import { auth } from '../auth';
import { modules } from '../modules';

const route = useRoute();
const mod = computed(() => modules.find((m) => m.key === route.params.key));
const rows = ref([]), meta = ref({ current_page: 1, last_page: 1, total: 0 });
const q = ref(''), loading = ref(false), error = ref(''), notice = ref('');
const form = ref(null), editingId = ref(null), saving = ref(false), fieldErrors = ref({}), options = ref({});

const canWrite = computed(() => auth.isHr);
const canCreate = computed(() => auth.isHr || mod.value?.selfCreate);
const fields = computed(() => mod.value.fields.filter((f) => auth.isHr || !f.hrOnly));
const rowActions = computed(() => (mod.value.rowActions || []).filter((a) => !a.hr || auth.isHr));
const toolbar = computed(() => (mod.value.toolbar || []).filter((a) => !a.hr || auth.isHr));
const hasActions = computed(() => canWrite.value || rowActions.value.length);

const get = (row, path) => path.split('.').reduce((o, k) => o?.[k], row);
const money = (v) => Number(v ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const tone = (v) => (['active', 'present', 'approved', 'paid', 'hired', 'open', 'acknowledged'].includes(v) ? 'ok'
  : ['pending', 'late', 'draft', 'screening', 'interview', 'offer', 'submitted'].includes(v) ? 'warn'
  : ['rejected', 'absent', 'inactive', 'closed'].includes(v) ? 'bad' : '');
function cell(row, c) {
  const v = get(row, c.key);
  if (v == null || v === '') return '—';
  if (c.money) return money(v);
  if (c.time) return String(v).slice(0, 5);
  if (c.yesNo) return v ? 'Yes' : 'No';
  return v;
}

async function load(page = 1) {
  loading.value = true; error.value = '';
  try {
    const { data } = await api.get(mod.value.endpoint, { params: { page, q: q.value || undefined } });
    rows.value = data.data; meta.value = data;
  } catch (e) { error.value = errMsg(e); } finally { loading.value = false; }
}
async function loadOptions() {
  for (const f of mod.value.fields.filter((f) => f.remote)) {
    try {
      const { data } = await api.get(f.remote, { params: { per_page: 100 } });
      options.value[f.name] = data.data.map((o) => ({ value: o.id, label: o[f.remoteLabel] }));
    } catch { options.value[f.name] = []; }
  }
}
const choices = (f) => f.options || options.value[f.name] || [];

function openForm(row = null) {
  fieldErrors.value = {}; editingId.value = row?.id ?? null;
  const v = {};
  for (const f of mod.value.fields) {
    let x = row ? row[f.name] : f.default ?? '';
    if (typeof x === 'boolean') x = Number(x);
    if (f.type === 'time' && x) x = String(x).slice(0, 5);
    v[f.name] = x ?? '';
  }
  form.value = v;
}
async function save() {
  saving.value = true; fieldErrors.value = {};
  const body = {};
  for (const f of fields.value) body[f.name] = form.value[f.name] === '' ? null : form.value[f.name];
  if (editingId.value) for (const f of mod.value.fields) if (!(f.name in body)) body[f.name] = form.value[f.name] === '' ? null : form.value[f.name];
  try {
    editingId.value ? await api.put(`${mod.value.endpoint}/${editingId.value}`, body) : await api.post(mod.value.endpoint, body);
    form.value = null; notice.value = 'Saved.'; load(meta.value.current_page);
  } catch (e) { fieldErrors.value = e.response?.data?.errors || {}; error.value = e.response?.status === 422 ? (e.response.data.message || '') : errMsg(e); }
  finally { saving.value = false; }
}
async function remove(row) {
  if (!confirm('Delete this record? This cannot be undone.')) return;
  try { await api.delete(`${mod.value.endpoint}/${row.id}`); notice.value = 'Deleted.'; load(meta.value.current_page); }
  catch (e) { error.value = errMsg(e); }
}
async function runAction(a, row) {
  error.value = notice.value = '';
  try { const out = await a.run(row); notice.value = typeof out === 'string' ? out : out === null ? '' : 'Done.'; if (out !== null) load(meta.value.current_page); }
  catch (e) { error.value = errMsg(e); }
}
function init() { q.value = ''; notice.value = error.value = ''; form.value = null; load(); loadOptions(); }
onMounted(init);
watch(() => route.params.key, init);
</script>

<template>
  <template v-if="mod">
    <h1>{{ mod.title }}</h1>
    <p class="sub">{{ mod.blurb }}</p>
    <div class="bar">
      <input type="search" v-model="q" placeholder="Search" aria-label="Search" @keyup.enter="load(1)">
      <button class="btn" @click="load(1)">Search</button>
      <span class="grow"></span>
      <button v-for="t in toolbar" :key="t.label" class="btn" :class="{ primary: t.primary }" @click="runAction(t)">{{ t.label }}</button>
      <button v-if="canCreate" class="btn primary" @click="openForm()">{{ mod.selfCreate && !auth.isHr ? 'Request leave' : 'Add new' }}</button>
    </div>
    <div v-if="notice" class="msg ok">{{ notice }}</div>
    <div v-if="error && !form" class="msg err">{{ error }}</div>

    <div class="tablewrap">
      <table>
        <thead><tr>
          <th v-for="c in mod.columns" :key="c.key" :class="{ num: c.num || c.money }">{{ c.label }}</th>
          <th v-if="hasActions"></th>
        </tr></thead>
        <tbody>
          <tr v-for="r in rows" :key="r.id">
            <td v-for="c in mod.columns" :key="c.key" :class="{ num: c.num || c.money }">
              <span v-if="c.badge" class="badge" :class="tone(get(r, c.key))">{{ get(r, c.key) }}</span>
              <template v-else>{{ cell(r, c) }}</template>
            </td>
            <td v-if="hasActions" class="acts">
              <template v-for="a in rowActions" :key="a.label">
                <button v-if="!a.show || a.show(r)" class="btn sm" :class="{ danger: a.danger }" @click="runAction(a, r)">{{ a.label }}</button>
              </template>
              <button v-if="canWrite" class="btn sm" @click="openForm(r)">Edit</button>
              <button v-if="canWrite" class="btn sm danger" @click="remove(r)">Delete</button>
            </td>
          </tr>
          <tr v-if="!rows.length"><td :colspan="mod.columns.length + 1" class="empty">{{ loading ? 'Loading…' : 'Nothing here yet.' }}</td></tr>
        </tbody>
      </table>
    </div>
    <div class="pager">
      {{ meta.total }} records
      <button class="btn sm" :disabled="meta.current_page <= 1" @click="load(meta.current_page - 1)">Previous</button>
      Page {{ meta.current_page }} of {{ meta.last_page }}
      <button class="btn sm" :disabled="meta.current_page >= meta.last_page" @click="load(meta.current_page + 1)">Next</button>
    </div>

    <div v-if="form" class="overlay" @click.self="form = null">
      <form class="modal" @submit.prevent="save" role="dialog" aria-modal="true">
        <h2>{{ editingId ? 'Edit' : 'Add' }} — {{ mod.title.toLowerCase() }}</h2>
        <div v-if="error" class="msg err">{{ error }}</div>
        <div class="grid2">
          <div v-for="f in fields" :key="f.name" class="field" :class="{ wide: f.wide || f.type === 'textarea' }">
            <label :for="f.name">{{ f.label }}</label>
            <select v-if="f.type === 'select'" :id="f.name" v-model="form[f.name]" :required="f.required">
              <option value="">{{ f.required ? 'Choose…' : 'None' }}</option>
              <option v-for="o in choices(f)" :key="o.value" :value="o.value">{{ o.label }}</option>
            </select>
            <textarea v-else-if="f.type === 'textarea'" :id="f.name" rows="3" v-model="form[f.name]"></textarea>
            <input v-else :id="f.name" :type="f.type || 'text'" :step="f.type === 'number' ? 'any' : undefined" v-model="form[f.name]" :required="f.required">
            <small v-if="fieldErrors[f.name]">{{ fieldErrors[f.name][0] }}</small>
          </div>
        </div>
        <div class="actions">
          <button type="button" class="btn" @click="form = null">Cancel</button>
          <button class="btn primary" :disabled="saving">{{ saving ? 'Saving…' : 'Save' }}</button>
        </div>
      </form>
    </div>
  </template>
  <p v-else>Page not found.</p>
</template>
