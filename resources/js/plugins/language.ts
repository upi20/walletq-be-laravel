import { useLanguage } from '@/composables/useLanguage';

export default {
  install() {
    // Initialize language from localStorage on app start
    const { initLanguage } = useLanguage();
    initLanguage();
  }
};