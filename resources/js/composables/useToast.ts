import { ref } from 'vue';
import type { Toast, ToastType, ToastOptions } from '@/types/toast';

const toasts = ref<Toast[]>([]);

export const useToast = () => {
  const generateId = (): string => {
    return `toast_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  };

  const addToast = (
    type: ToastType,
    title: string,
    options: ToastOptions = {}
  ): string => {
    const toast: Toast = {
      id: generateId(),
      type,
      title,
      message: options.message,
      duration: options.duration ?? (type === 'error' ? 6000 : 4000),
      persistent: options.persistent ?? false,
      createdAt: new Date(),
    };

    toasts.value.push(toast);

    // Auto remove toast after duration
    if (!toast.persistent && toast.duration && toast.duration > 0) {
      setTimeout(() => {
        removeToast(toast.id);
      }, toast.duration);
    }

    return toast.id;
  };

  const removeToast = (id: string): void => {
    const index = toasts.value.findIndex(toast => toast.id === id);
    if (index > -1) {
      toasts.value.splice(index, 1);
    }
  };

  const clearAllToasts = (): void => {
    toasts.value = [];
  };

  // Convenience methods
  const success = (title: string, options?: ToastOptions): string => {
    return addToast('success', title, options);
  };

  const error = (title: string, options?: ToastOptions): string => {
    return addToast('error', title, options);
  };

  const warning = (title: string, options?: ToastOptions): string => {
    return addToast('warning', title, options);
  };

  const info = (title: string, options?: ToastOptions): string => {
    return addToast('info', title, options);
  };

  return {
    toasts,
    addToast,
    removeToast,
    clearAllToasts,
    success,
    error,
    warning,
    info,
  };
};