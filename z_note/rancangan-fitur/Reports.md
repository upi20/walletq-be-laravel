# Rancangan Fitur Reports (Laporan Keuangan)

> **📄 Dokumen Lengkap**: Lihat [reports_specification.md](/Users/upi/.gemini/antigravity/brain/7a93bc2a-f3c0-40ca-9e3a-021d7c04e401/reports_specification.md) untuk spesifikasi detail lengkap

## 🎯 Overview

Fitur Reports adalah halaman laporan keuangan yang menampilkan visualisasi pendapatan dan pengeluaran per kategori dalam bentuk pie chart, dengan drill-down capability untuk melihat detail transaksi.

## 📊 Struktur Halaman

### 1. `/reports` - Halaman Utama
- **2 Pie Charts**: Pendapatan & Pengeluaran (breakdown per kategori)
- **Filter Full**: Sama seperti `/transactions` (9 jenis filter)
- **Summary Cards**: Total Pendapatan, Total Pengeluaran, Selisih
- **Action Buttons**: "Lihat Detail Pendapatan" & "Lihat Detail Pengeluaran"

### 2. `/reports/income` - Detail Pendapatan
- **List Kategori Pendapatan**: Sorted by amount DESC
- **Filter Full**: Sama seperti `/transactions` (type auto-set ke 'income')
- **Summary**: Total, Jumlah Kategori, Jumlah Transaksi
- **Click Action**: Navigasi ke `/reports/income/{categoryId}/transactions`

### 3. `/reports/expense` - Detail Pengeluaran
- **List Kategori Pengeluaran**: Sorted by amount DESC
- **Filter Full**: Sama seperti `/transactions` (type auto-set ke 'expense')
- **Summary**: Total, Jumlah Kategori, Jumlah Transaksi
- **Click Action**: Navigasi ke `/reports/expense/{categoryId}/transactions`

### 4. `/reports/{type}/{categoryId}/transactions` - Transaksi per Kategori
- **Reuse Component**: Sama persis dengan `/transactions`
- **Auto Filter**: type + category_ids sudah ter-set
- **Back Button**: Kembali ke detail income/expense

## 🔑 Key Features

### Filter System
**Menggunakan SEMUA filter yang sama dengan `/transactions`:**
1. Period (today, week, month, year, all, custom)
2. Date Range (date_from, date_to, month, year)
3. Type (both, income, expense)
4. Search (note, account name, category name)
5. Account IDs (multiple)
6. Category IDs (multiple)
7. Tag IDs (multiple)
8. Flags (multiple)
9. Amount Range (min, max)

### Data Rules
- ✅ **Hanya kategori dengan total > 0** yang ditampilkan di pie chart
- ✅ **Sorted by amount DESC** di halaman detail
- ✅ **Filter persistence** saat navigasi antar halaman
- ✅ **URL query parameters** untuk semua filter

## 🛠️ Technical Stack

### Backend (NEW)
- `app/Http/Controllers/Web/Reports/ReportController.php`
- `app/Services/Report/ReportService.php`
- Routes di `routes/web.php`

### Frontend (NEW)
- `resources/js/Pages/Reports/Index.vue`
- `resources/js/Pages/Reports/IncomeDetail.vue`
- `resources/js/Pages/Reports/ExpenseDetail.vue`
- `resources/js/Pages/Reports/CategoryTransactions.vue`
- `resources/js/components/PieChart.vue`

### Frontend (REUSE)
- `SearchModal.vue`, `FilterModal.vue`, `TransactionCard.vue`

### Library
```bash
npm install chart.js vue-chartjs
```

## 🎨 Design System

- **Income**: Teal gradient (`from-teal-500 to-teal-600`)
- **Expense**: Coral gradient (`from-coral-500 to-coral-600`)
- **Cards**: `rounded-2xl` dengan `shadow-md`
- **Mobile-First**: Single column, touch-optimized (min 44px)
- **Dark Mode**: Full support

## 📝 Database Analysis (Actual Data)

**Dari analisis MCP:**
- 15 kategori income, 40 kategori expense
- Contoh data income: "Gaji Maxximum" (8 transaksi, Rp 46.3jt), "Terima Saldo" (116 transaksi, Rp 46.4jt)
- Flags yang ada: normal, transfer_in, transfer_out, debt_payment, debt_collect, initial_balance

## ✅ Acceptance Criteria

1. ✅ Pie chart hanya menampilkan kategori dengan nominal > 0
2. ✅ Filter system identik dengan `/transactions`
3. ✅ Navigasi preserve filter state
4. ✅ List kategori sorted by amount DESC
5. ✅ Reuse component dari `/transactions`
6. ✅ Mobile-first design
7. ✅ Dark mode support
8. ✅ Loading & empty states

---

**Catatan**: Dokumen ini adalah ringkasan. Untuk spesifikasi teknis lengkap, controller methods, service methods, dan layout detail, lihat `reports_specification.md`.
