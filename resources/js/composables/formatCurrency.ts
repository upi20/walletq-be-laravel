/**
 * Format currency in Indonesian Rupiah
 * @param amount - The amount to format
 * @param type - Format type: 'decimal' (no currency symbol) or 'currency' (with Rp symbol)
 * @param preserveSign - Whether to preserve negative sign for negative amounts
 */
export default function formatCurrency(
  amount: number, 
  type: string = 'decimal', 
  preserveSign: boolean = true
): string {
  if (isNaN(amount) || amount === null || amount === undefined) {
    return '0';
  }

  // Determine if the amount is negative
  const isNegative = amount < 0;
  
  // Use absolute value for formatting
  const absAmount = Math.abs(amount);
  
  let formattedAmount: string;
  
  if (type === 'decimal') {
    // Return formatted number without currency symbol
    formattedAmount = new Intl.NumberFormat('id-ID').format(absAmount);
  } else {
    // Default: return with currency symbol
    formattedAmount = new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(absAmount);
  }
  
  // Add negative sign if needed and preserveSign is true
  if (isNegative && preserveSign) {
    return `-${formattedAmount}`;
  }
  
  return formattedAmount;
}