import { ref } from 'vue';
import type { ConfirmationOptions, ConfirmationDialog, ConfirmationResult } from '@/types/confirmation';

const activeDialog = ref<ConfirmationDialog | null>(null);

export const useConfirmation = () => {
  const generateId = (): string => {
    return `confirmation_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  };

  const showConfirmation = (options: ConfirmationOptions): Promise<boolean> => {
    return new Promise((resolve) => {
      // Close any existing dialog first
      if (activeDialog.value) {
        activeDialog.value.resolve(false);
      }

      const dialog: ConfirmationDialog = {
        id: generateId(),
        options: {
          type: 'info',
          confirmText: 'Konfirmasi',
          cancelText: 'Batal',
          showIcon: true,
          persistent: false,
          ...options
        },
        resolve,
        createdAt: new Date()
      };

      activeDialog.value = dialog;
    });
  };

  const closeDialog = (confirmed: boolean = false): void => {
    if (activeDialog.value) {
      activeDialog.value.resolve(confirmed);
      activeDialog.value = null;
    }
  };

  // Convenience methods for different types
  const confirmDelete = (itemName?: string): Promise<boolean> => {
    return showConfirmation({
      title: 'Konfirmasi Hapus',
      message: itemName 
        ? `Apakah Anda yakin ingin menghapus "${itemName}"? Tindakan ini tidak dapat dibatalkan.`
        : 'Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.',
      type: 'danger',
      confirmText: 'Hapus',
      cancelText: 'Batal',
      confirmButtonClass: 'bg-red-600 hover:bg-red-700 text-white'
    });
  };

  const confirmAction = (title: string, message?: string): Promise<boolean> => {
    return showConfirmation({
      title,
      message,
      type: 'warning',
      confirmText: 'Ya, Lanjutkan',
      cancelText: 'Batal'
    });
  };

  const confirmInfo = (title: string, message?: string): Promise<boolean> => {
    return showConfirmation({
      title,
      message,
      type: 'info',
      confirmText: 'OK',
      cancelText: 'Batal'
    });
  };

  const confirmSuccess = (title: string, message?: string): Promise<boolean> => {
    return showConfirmation({
      title,
      message,
      type: 'success',
      confirmText: 'Lanjutkan',
      cancelText: 'Kembali'
    });
  };

  const alert = (title: string, message?: string): Promise<boolean> => {
    return showConfirmation({
      title,
      message,
      type: 'info',
      confirmText: 'OK',
      cancelText: '',
      persistent: true
    });
  };

  return {
    activeDialog,
    showConfirmation,
    closeDialog,
    confirmDelete,
    confirmAction,
    confirmInfo,
    confirmSuccess,
    alert
  };
};