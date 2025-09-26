import { ref, computed } from 'vue';

export type Language = 'id' | 'en';

// Global reactive state
const currentLanguage = ref<Language>('id'); // Default bahasa Indonesia

// Language options
export const languageOptions = [
  { value: 'id', label: 'Bahasa Indonesia', flag: '🇮🇩' },
  { value: 'en', label: 'English', flag: '🇺🇸' }
] as const;

export function useLanguage() {
  // Get current language
  const language = computed(() => currentLanguage.value);

  // Set language
  const setLanguage = (lang: Language) => {
    currentLanguage.value = lang;
    // Save to localStorage for persistence
    localStorage.setItem('app_language', lang);
  };

  // Initialize from localStorage if available
  const initLanguage = () => {
    const saved = localStorage.getItem('app_language') as Language;
    if (saved && ['id', 'en'].includes(saved)) {
      currentLanguage.value = saved;
    }
  };

  // Get current language option
  const currentLanguageOption = computed(() => {
    return languageOptions.find(option => option.value === currentLanguage.value);
  });

  return {
    language,
    setLanguage,
    initLanguage,
    languageOptions,
    currentLanguageOption
  };
}