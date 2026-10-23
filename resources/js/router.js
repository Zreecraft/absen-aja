import { createRouter, createWebHistory } from 'vue-router';

const routes = [
  { path: '/', redirect: '/kelas' },

  {
    path: '/kelas',
    component: () => import('./pages/KelasList.vue'),
  },
  {
    path: '/kelas/:id',
    component: () => import('./pages/KelasDetail.vue'),
  },
  {
    path: '/kelas/:id/pertemuan/:pid',
    component: () => import('./pages/InputKehadiran.vue'),
  },
  {
    path: '/mahasiswa',
    component: () => import('./pages/MahasiswaList.vue'),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
