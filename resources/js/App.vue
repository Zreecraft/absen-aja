<template>
  <div class="min-h-screen bg-slate-50 flex flex-col text-slate-800">
    <!-- Sidebar Desktop & Mobile Drawer -->
    <AppSidebar :mobile-open="mobileNavOpen" @update:mobile-open="mobileNavOpen = $event" />

    <!-- Topbar Mobile (layar < lg) -->
    <header class="lg:hidden sticky top-0 z-20 bg-white border-b border-slate-200 px-4 h-16 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <button
          type="button"
          @click="mobileNavOpen = true"
          class="p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
          aria-label="Buka menu navigasi"
        >
          <Menu class="w-5 h-5" />
        </button>
        <router-link to="/kelas" class="flex items-center gap-2 font-bold text-slate-900 text-base">
          <div class="w-7 h-7 rounded-md bg-brand-600 text-white flex items-center justify-center">
            <ClipboardCheck class="w-4 h-4" />
          </div>
          <span>Absen Aja</span>
        </router-link>
      </div>
    </header>

    <!-- Area Konten Utama -->
    <main class="lg:pl-64 flex-1 flex flex-col min-w-0">
      <div class="max-w-6xl w-full mx-auto p-4 sm:p-6 lg:p-8 flex-1">
        <router-view />
      </div>
    </main>

    <!-- Host Notifikasi Toast Global -->
    <ToastHost />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import AppSidebar from './components/AppSidebar.vue';
import ToastHost from './components/ToastHost.vue';
import { Menu, ClipboardCheck } from 'lucide-vue-next';

const mobileNavOpen = ref(false);
</script>
