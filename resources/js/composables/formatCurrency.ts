export default function formatCurrency(value: number, locale = 'id-ID', currency = 'IDR'): string {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
    }).format(value);
}