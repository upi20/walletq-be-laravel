# Analisis Data Transaksi - Requirement & Dependencies

## Tanggal: 23 September 2025

## Data Yang Diperlukan Untuk Melakukan Transaksi

### 1. **Data Wajib (Required)**
```json
{
  "account_id": "integer - ID rekening tujuan",
  "transaction_category_id": "integer - ID kategori transaksi", 
  "type": "string - income/expense",
  "amount": "decimal - jumlah nominal",
  "date": "date - tanggal transaksi"
}
```

### 2. **Data Opsional**
```json
{
  "note": "string - catatan transaksi",
  "tags": "array - ID tag yang terkait",
  "source_type": "string - jenis sumber (untuk polymorphic)",
  "source_id": "integer - ID sumber (untuk polymorphic)"
}
```

### 3. **Data Yang Di-generate Otomatis**
```json
{
  "user_id": "dari authenticated user",
  "flag": "default 'normal', bisa transfer_in/out, debt_payment, etc",
  "import_id": "null kecuali dari import file",
  "created_at": "timestamp",
  "updated_at": "timestamp"
}
```

## Tabel Pendukung Yang Terlibat

### 1. **Tabel Master (Harus Ada Data)**

#### **accounts**
- **Role**: Rekening sumber/tujuan transaksi
- **Data needed**: 
  - `id` - untuk account_id
  - `current_balance` - untuk validasi saldo
  - `user_id` - untuk validasi ownership
- **Impact**: Saldo akan terupdate otomatis

#### **transaction_categories** 
- **Role**: Kategori transaksi (makanan, transport, gaji, dll)
- **Data needed**:
  - `id` - untuk transaction_category_id
  - `type` - harus match dengan transaction.type
  - `user_id` - untuk validasi ownership

#### **users**
- **Role**: Owner transaksi
- **Data needed**:
  - `id` - untuk user_id
  - `balance` - akan terupdate otomatis

### 2. **Tabel Relasional (Opsional)**

#### **tags**
- **Role**: Label/tag untuk kategorisasi tambahan
- **Relationship**: Many-to-Many via `transaction_tags`
- **Data needed**:
  - `id` - untuk attach ke transaksi
  - `user_id` - untuk validasi ownership

#### **transaction_tags**
- **Role**: Junction table untuk tags
- **Auto-managed**: Ya, via Eloquent relationship

### 3. **Tabel Audit/Import (Conditional)**

#### **import_transactions**
- **Role**: Tracking jika transaksi dari import file
- **When needed**: Hanya jika transaksi berasal dari import
- **Data**: `id` untuk import_id

## Flow Proses Transaksi

### 1. **Validasi Input**
```
✓ account_id exists & belongs to user
✓ transaction_category_id exists & belongs to user  
✓ type matches category.type
✓ amount > 0
✓ date is valid
✓ tags exist & belong to user (if provided)
```

### 2. **Validasi Business Logic**
```
✓ Jika expense: cek sufficient balance
✓ Account lock untuk prevent race condition
✓ User ownership validation
```

### 3. **Data Updates**
```
1. INSERT transaction record
2. UPDATE account.current_balance
3. UPDATE user.balance (total dari semua accounts)
4. INSERT transaction_tags (jika ada tags)
```

## Jenis Transaksi Berdasarkan Flag

### **Normal Transaction (flag: 'normal')**
- Transaksi income/expense biasa
- Data minimal: account, category, type, amount, date

### **Transfer (flag: 'transfer_in'/'transfer_out')**
- Membutuhkan data transfer di tabel `transfers`
- Polymorphic relationship: source_type='Transfer', source_id=transfer.id
- 2 transaksi: satu expense (out), satu income (in)

### **Debt Related (flag: 'debt_payment'/'debt_collect')**
- Membutuhkan data di tabel `debts`
- Polymorphic relationship: source_type='Debt', source_id=debt.id

### **Initial Balance (flag: 'initial_balance')**
- Saldo awal account
- Category khusus: "Saldo Awal"

## Dependencies untuk Fitur Lanjutan

### **Transfer Antar Rekening**
**Tabel terlibat:**
- `transfers` - record transfer
- `transactions` (2 records) - expense & income
- `accounts` (2 records) - from & to account

### **Debt/Receivable Payment**
**Tabel terlibat:**
- `debts` - record utang/piutang
- `transactions` - payment record
- `accounts` - account sumber pembayaran

### **Import Bulk Transaction**
**Tabel terlibat:**
- `import_transactions` - batch record
- `transactions` - individual records
- `accounts` & `transaction_categories` - validation

## Kesimpulan

Untuk transaksi dasar, minimal diperlukan data dari:
- **accounts** (rekening)
- **transaction_categories** (kategori)
- **users** (owner)

Untuk fitur advanced, perlu data dari:
- **tags** (labeling)
- **transfers** (transfer antar rekening)
- **debts** (utang piutang)
- **import_transactions** (bulk import)