<template>
  <span
    :class="[
      'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium border',
      statusInfo.badgeClass
    ]"
  >
    <span :class="['w-1.5 h-1.5 rounded-full shrink-0', statusInfo.dotClass]"></span>
    <span>{{ statusInfo.label }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue';
import { getStatus } from '../status.js';

const props = defineProps({
  status: {
    type: String,
    default: '',
  },
});

const statusInfo = computed(() => getStatus(props.status));
</script>
