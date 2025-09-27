/**
 * Format currency in Indonesian Rupiah
 */
export default function formatCurrency(amount: number, type: string = 'decimal'): string {
  if (isNaN(amount) || amount === null || amount === undefined) {
    return '0';
  }

  // Convert to absolute value for formatting
  const absAmount = Math.abs(amount);
  
  if (type === 'decimal') {
    // Return formatted number without currency symbol
    return new Intl.NumberFormat('id-ID').format(absAmount);
  }
  
  // Default: return with currency symbol
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(absAmount);
}