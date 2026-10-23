/**
 * useToast.js
 * Toast composable sederhana dengan state di level modul.
 * Tanpa library state management eksternal.
 */

import { ref } from 'vue';

const toasts = ref([]);
let nextToastId = 1;

export function useToast() {
  function addToast({ message, type = 'success', duration = 3500 }) {
    const id = nextToastId++;
    const item = { id, message, type };
    toasts.value.push(item);

    if (duration > 0) {
      setTimeout(() => {
        removeToast(id);
      }, duration);
    }
    return id;
  }

  function success(message, duration = 3500) {
    return addToast({ message, type: 'success', duration });
  }

  function error(message, duration = 4500) {
    return addToast({ message, type: 'error', duration });
  }

  function removeToast(id) {
    toasts.value = toasts.value.filter((t) => t.id !== id);
  }

  return {
    toasts,
    addToast,
    success,
    error,
    removeToast,
  };
}
