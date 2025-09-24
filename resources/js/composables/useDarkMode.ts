import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

// Global dark mode state
const isDarkMode = ref(false);

export function useDarkMode() {
  // Initialize from localStorage or system preference
  const initializeDarkMode = () => {
    const stored = localStorage.getItem('darkMode');
    if (stored !== null) {
      isDarkMode.value = JSON.parse(stored);
    } else {
      // Check system preference
      isDarkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    
    // Apply to document
    updateDocumentClass();
  };

  // Update document class and localStorage
  const updateDocumentClass = () => {
    if (isDarkMode.value) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
    localStorage.setItem('darkMode', JSON.stringify(isDarkMode.value));
  };

  // Toggle dark mode
  const toggle = () => {
    isDarkMode.value = !isDarkMode.value;
    updateDocumentClass();
  };

  // Watch for changes
  watch(isDarkMode, updateDocumentClass);

  // Initialize on first use
  if (typeof window !== 'undefined') {
    initializeDarkMode();
  }

  return {
    isDarkMode: computed(() => isDarkMode.value),
    toggle,
  };
}