import axios from 'axios';
import { auth } from './auth';

const api = axios.create({ baseURL: '/api', headers: { Accept: 'application/json' } });
api.interceptors.request.use((c) => { if (auth.token) c.headers.Authorization = `Bearer ${auth.token}`; return c; });
api.interceptors.response.use((r) => r, (e) => {
  if (e.response?.status === 401 && auth.token) { auth.clear(); location.href = '/login'; }
  return Promise.reject(e);
});
export const errMsg = (e) => e.response?.data?.message || 'Something went wrong. Try again.';
export default api;
