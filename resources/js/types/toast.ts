export type ToastType = 'success' | 'error' | 'warning' | 'info';

export interface Toast {
  id: string;
  type: ToastType;
  title: string;
  message?: string;
  duration?: number;
  persistent?: boolean;
  createdAt: Date;
}

export interface ToastOptions {
  message?: string;
  duration?: number;
  persistent?: boolean;
}