<template>
  <ModalDialog :show="show" :title="title" @close="emit('cancel')">
    <div class="flex items-start gap-3 py-1">
      <div
        :class="[
          'p-2 rounded-full shrink-0',
          danger ? 'bg-rose-100 text-rose-600' : 'bg-brand-100 text-brand-600'
        ]"
      >
        <AlertTriangle v-if="danger" class="w-5 h-5" />
        <Info v-else class="w-5 h-5" />
      </div>
      <div class="text-sm text-slate-600 leading-relaxed pt-0.5">
        {{ message }}
      </div>
    </div>

    <template #footer>
      <button
        type="button"
        :disabled="loading"
        @click="emit('cancel')"
        class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 disabled:opacity-50"
      >
        {{ cancelText }}
      </button>
      <button
        type="button"
        :disabled="loading"
        @click="emit('confirm')"
        :class="[
          'px-4 py-2 text-sm font-medium text-white rounded-lg focus-visible:outline-none focus-visible:ring-2 disabled:opacity-50 flex items-center gap-2',
          danger
            ? 'bg-rose-600 hover:bg-rose-700 focus-visible:ring-rose-500'
            : 'bg-brand-600 hover:bg-brand-700 focus-visible:ring-brand-500'
        ]"
      >
        <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
        <span>{{ loading ? 'Memproses...' : confirmText }}</span>
      </button>
    </template>
  </ModalDialog>
</template>

<script setup>
import ModalDialog from './ModalDialog.vue';
import { AlertTriangle, Info, Loader2 } from 'lucide-vue-next';

defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Konfirmasi',
  },
  message: {
    type: String,
    required: true,
  },
  confirmText: {
    type: String,
    default: 'Hapus',
  },
  cancelText: {
    type: String,
    default: 'Batal',
  },
  danger: {
    type: Boolean,
    default: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['confirm', 'cancel']);
</script>
