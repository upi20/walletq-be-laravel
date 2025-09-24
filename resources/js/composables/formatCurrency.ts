export default function formatCurrency(value: number, locale = 'en-US', currency = 'USD'): string {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
    }).format(value);
}