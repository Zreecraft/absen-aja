<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs transition-opacity"
      role="dialog"
      aria-modal="true"
      :aria-labelledby="titleId"
      @click="handleBackdropClick"
    >
      <div
        ref="dialogBox"
        class="bg-white rounded-xl border border-slate-200 shadow-lg max-w-lg w-full overflow-hidden flex flex-col max-h-[90vh]"
        @click.stop
      >
        <!-- Header Modal -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/50">
          <h3 :id="titleId" class="text-base font-semibold text-slate-800">
            {{ title }}
          </h3>
          <button
            type="button"
            @click="emit('close')"
            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
            title="Tutup dialog (Esc)"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Body Modal -->
        <div class="px-5 py-4 overflow-y-auto">
          <slot />
        </div>

        <!-- Footer Modal (opsional) -->
        <div v-if="$slots.footer" class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-2">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    required: true,
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['close']);

const titleId = `modal-title-${Math.random().toString(36).substring(2, 9)}`;
const dialogBox = ref(null);

function handleBackdropClick() {
  if (props.closeOnBackdrop) {
    emit('close');
  }
}

function handleKeydown(e) {
  if (props.show && e.key === 'Escape') {
    emit('close');
  }
}

watch(
  () => props.show,
  async (isShown) => {
    if (isShown) {
      await nextTick();
      if (dialogBox.value) {
        const focusable = dialogBox.value.querySelector(
          'input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), button:not([disabled])'
        );
        if (focusable) {
          focusable.focus();
        }
      }
    }
  }
);

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);
});
</script>
