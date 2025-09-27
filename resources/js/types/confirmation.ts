export type ConfirmationType = 'danger' | 'warning' | 'info' | 'success';

export interface ConfirmationOptions {
  title: string;
  message?: string;
  type?: ConfirmationType;
  confirmText?: string;
  cancelText?: string;
  confirmButtonClass?: string;
  cancelButtonClass?: string;
  persistent?: boolean;
  showIcon?: boolean;
}

export interface ConfirmationDialog {
  id: string;
  options: ConfirmationOptions;
  resolve: (confirmed: boolean) => void;
  createdAt: Date;
}

export interface ConfirmationResult {
  confirmed: boolean;
  dismissed: boolean;
}