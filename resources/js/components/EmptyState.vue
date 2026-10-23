<template>
  <div class="text-center py-12 px-4 rounded-xl border border-dashed border-slate-200 bg-white/50">
    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
      <component :is="iconComponent" class="w-6 h-6" />
    </div>
    <h3 class="text-sm font-semibold text-slate-800 mb-1">
      {{ title }}
    </h3>
    <p v-if="description" class="text-sm text-slate-500 max-w-sm mx-auto mb-4">
      {{ description }}
    </p>
    <div v-if="actionText" class="mt-2">
      <button
        type="button"
        @click="emit('action')"
        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
      >
        <Plus class="w-4 h-4" />
        <span>{{ actionText }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Inbox, Plus } from 'lucide-vue-next';

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    default: '',
  },
  actionText: {
    type: String,
    default: '',
  },
  icon: {
    type: [Object, Function],
    default: null,
  },
});

const emit = defineEmits(['action']);

const iconComponent = computed(() => props.icon || Inbox);
</script>
