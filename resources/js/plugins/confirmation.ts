import type { App } from 'vue';
import { useConfirmation } from '@/composables/useConfirmation';

export default {
  install(app: App) {
    const confirmation = useConfirmation();
    
    app.config.globalProperties.$confirm = confirmation;
    app.provide('confirmation', confirmation);
  }
};