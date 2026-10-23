<template>
  <div
    class="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none px-4 sm:px-0"
    aria-live="polite"
    aria-atomic="true"
  >
    <div
      v-for="toast in toasts"
      :key="toast.id"
      :class="[
        'pointer-events-auto flex items-start gap-3 p-3.5 rounded-lg border shadow-sm text-sm transition-all duration-200',
        toast.type === 'error'
          ? 'bg-rose-50 border-rose-200 text-rose-800'
          : 'bg-emerald-50 border-emerald-200 text-emerald-800'
      ]"
      role="alert"
    >
      <component
        :is="toast.type === 'error' ? AlertCircle : CheckCircle2"
        class="w-5 h-5 shrink-0 mt-0.5"
        :class="toast.type === 'error' ? 'text-rose-600' : 'text-emerald-600'"
      />
      <div class="flex-1 font-medium leading-snug break-words">
        {{ toast.message }}
      </div>
      <button
        type="button"
        @click="removeToast(toast.id)"
        class="shrink-0 p-1 rounded hover:bg-black/5 text-slate-500 hover:text-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
        title="Tutup notifikasi"
      >
        <X class="w-4 h-4" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { useToast } from '../useToast.js';
import { AlertCircle, CheckCircle2, X } from 'lucide-vue-next';

const { toasts, removeToast } = useToast();
</script>
