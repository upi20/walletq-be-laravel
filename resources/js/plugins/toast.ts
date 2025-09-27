import type { App } from 'vue';
import { useToast } from '@/composables/useToast';

export default {
  install(app: App) {
    const toast = useToast();
    
    app.config.globalProperties.$toast = toast;
    app.provide('toast', toast);
  }
};