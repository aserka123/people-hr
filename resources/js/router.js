import { createRouter, createWebHistory } from 'vue-router';
import { auth } from './auth';
import Login from './pages/Login.vue';
import Dashboard from './pages/Dashboard.vue';
import ModulePage from './pages/ModulePage.vue';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', component: Login, meta: { guest: true } },
    { path: '/', component: Dashboard },
    { path: '/m/:key', component: ModulePage },
  ],
});
router.beforeEach((to) => {
  if (!to.meta.guest && !auth.token) return '/login';
  if (to.meta.guest && auth.token) return '/';
});
export default router;
