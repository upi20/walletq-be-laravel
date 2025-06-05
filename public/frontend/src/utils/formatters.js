/**
 * Format a number as Indonesian currency (IDR)
 * @param {number} amount - The amount to format
 * @returns {string} Formatted currency string
 */
export function formatCurrency(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount);
}

/**
 * Format a date string to Indonesian format
 * @param {string} dateString - Date string in YYYY-MM-DD format
 * @returns {string} Formatted date string
 */
export function formatDate(dateString) {
  const options = { 
    day: '2-digit', 
    month: 'long', 
    year: 'numeric' 
  };
  
  return new Date(dateString).toLocaleDateString('id-ID', options);
}

/**
 * Format a date string to short Indonesian format
 * @param {string} dateString - Date string in YYYY-MM-DD format
 * @returns {string} Formatted date string
 */
export function formatShortDate(dateString) {
  const options = { 
    day: '2-digit', 
    month: '2-digit', 
    year: 'numeric' 
  };
  
  return new Date(dateString).toLocaleDateString('id-ID', options);
}

/**
 * Format a date with time
 * @param {string} dateString - Date string in ISO format
 * @returns {string} Formatted date and time string
 */
export function formatDateTime(dateString) {
  const options = { 
    day: '2-digit',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  };
  
  return new Date(dateString).toLocaleDateString('id-ID', options);
}
