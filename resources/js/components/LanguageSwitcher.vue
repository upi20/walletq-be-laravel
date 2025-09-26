<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Languages, ChevronDown } from 'lucide-vue-next';
import { useLanguage } from '@/composables/useLanguage';

const { language, setLanguage, languageOptions, currentLanguageOption } = useLanguage();

const isOpen = ref(false);

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const selectLanguage = (langValue: 'id' | 'en') => {
  setLanguage(langValue);
  isOpen.value = false;
};

// Close dropdown when clicking outside
const dropdownRef = ref<HTMLElement>();

onMounted(() => {
  document.addEventListener('click', (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
      isOpen.value = false;
    }
  });
});
</script>

<template>
  <div class="relative" ref="dropdownRef">
    <button
      @click="toggleDropdown"
      class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-colors"
    >
      <Languages class="w-4 h-4" />
      <span class="hidden sm:inline">{{ currentLanguageOption?.flag }}</span>
      <span class="hidden md:inline">{{ currentLanguageOption?.label }}</span>
      <ChevronDown class="w-4 h-4" />
    </button>

    <!-- Dropdown -->
    <div
      v-if="isOpen"
      class="absolute right-0 top-full mt-1 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50"
    >
      <button
        v-for="option in languageOptions"
        :key="option.value"
        @click="selectLanguage(option.value)"
        :class="[
          'w-full flex items-center gap-3 px-4 py-2 text-sm text-left transition-colors',
          language === option.value
            ? 'bg-teal-50 dark:bg-teal-900/20 text-teal-600 dark:text-teal-400'
            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
        ]"
      >
        <span class="text-base">{{ option.flag }}</span>
        <span>{{ option.label }}</span>
        <span
          v-if="language === option.value"
          class="ml-auto w-2 h-2 bg-teal-500 rounded-full"
        ></span>
      </button>
    </div>
  </div>
</template>