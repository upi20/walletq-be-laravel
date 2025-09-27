export default function formatCurrency(value: number, format: 'currency' | 'decimal' = 'decimal', locale = 'id-ID', currency = 'IDR'): string {
    return new Intl.NumberFormat(locale, {
        style: format === 'currency' ? 'currency' : 'decimal',
        currency,
        minimumFractionDigits: 0,
    }).format(value);
}