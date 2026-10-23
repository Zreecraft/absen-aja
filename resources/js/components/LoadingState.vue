<template>
  <div class="py-12 px-4 flex flex-col items-center justify-center text-center">
    <Loader2 class="w-8 h-8 text-brand-600 animate-spin mb-3" />
    <p class="text-sm text-slate-500 font-medium">
      {{ text }}
    </p>
  </div>
</template>

<script setup>
import { Loader2 } from 'lucide-vue-next';

defineProps({
  text: {
    type: String,
    default: 'Memuat data...',
  },
});
</script>
