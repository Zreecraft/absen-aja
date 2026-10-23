<template>
  <div>
    <!-- Sidebar Desktop (layar >= lg) -->
    <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 border-r border-slate-200 bg-white z-30">
      <!-- Logo App -->
      <div class="h-16 flex items-center px-6 border-b border-slate-100">
        <router-link to="/kelas" class="flex items-center gap-2.5 font-bold text-lg text-slate-900 group">
          <div class="w-8 h-8 rounded-lg bg-brand-600 text-white flex items-center justify-center shadow-xs group-hover:bg-brand-700 transition-colors">
            <ClipboardCheck class="w-5 h-5" />
          </div>
          <span>Absen Aja</span>
        </router-link>
      </div>

      <!-- Menu Navigasi -->
      <nav class="flex-1 p-4 space-y-1">
        <router-link
          to="/kelas"
          :class="[
            'flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors',
            $route.path.startsWith('/kelas')
              ? 'bg-brand-50 text-brand-700 font-semibold'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
          ]"
        >
          <GraduationCap class="w-5 h-5 shrink-0" />
          <span>Daftar Kelas</span>
        </router-link>

        <router-link
          to="/mahasiswa"
          :class="[
            'flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors',
            $route.path.startsWith('/mahasiswa')
              ? 'bg-brand-50 text-brand-700 font-semibold'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
          ]"
        >
          <Users class="w-5 h-5 shrink-0" />
          <span>Data Mahasiswa</span>
        </router-link>
      </nav>

      <!-- Footer Sidebar Desktop -->
      <div class="p-4 border-t border-slate-100 text-xs text-slate-400">
        Absen Aja &bull; Sistem Presensi
      </div>
    </aside>

    <!-- Mobile Drawer / Overlay (layar < lg) -->
    <div
      v-if="mobileOpen"
      class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-xs lg:hidden"
      @click="emit('update:mobileOpen', false)"
    ></div>

    <div
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 transform transition-transform duration-200 ease-in-out lg:hidden flex flex-col',
        mobileOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <div class="h-16 flex items-center justify-between px-6 border-b border-slate-100">
        <router-link
          to="/kelas"
          @click="emit('update:mobileOpen', false)"
          class="flex items-center gap-2.5 font-bold text-lg text-slate-900"
        >
          <div class="w-8 h-8 rounded-lg bg-brand-600 text-white flex items-center justify-center">
            <ClipboardCheck class="w-5 h-5" />
          </div>
          <span>Absen Aja</span>
        </router-link>
        <button
          type="button"
          @click="emit('update:mobileOpen', false)"
          class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <nav class="flex-1 p-4 space-y-1">
        <router-link
          to="/kelas"
          @click="emit('update:mobileOpen', false)"
          :class="[
            'flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors',
            $route.path.startsWith('/kelas')
              ? 'bg-brand-50 text-brand-700 font-semibold'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
          ]"
        >
          <GraduationCap class="w-5 h-5 shrink-0" />
          <span>Daftar Kelas</span>
        </router-link>

        <router-link
          to="/mahasiswa"
          @click="emit('update:mobileOpen', false)"
          :class="[
            'flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors',
            $route.path.startsWith('/mahasiswa')
              ? 'bg-brand-50 text-brand-700 font-semibold'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
          ]"
        >
          <Users class="w-5 h-5 shrink-0" />
          <span>Data Mahasiswa</span>
        </router-link>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { ClipboardCheck, GraduationCap, Users, X } from 'lucide-vue-next';

defineProps({
  mobileOpen: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:mobileOpen']);
</script>
