# MVP Transaksi - Contoh Request Postman

## Tanggal: 23 September 2025

## Setup Postman Collection

### **Base URL**
```
http://localhost:8000/api/v1
```

### **Authentication**
Semua request memerlukan JWT token di header:
```
Authorization: Bearer {your_jwt_token}
```

## 1. Transaksi Keluar (Expense) - Pembelian/Belanja

### **Method:** POST
### **Endpoint:** 
```
/user/transactions
```

### **Headers:**
```
Content-Type: application/json
Authorization: Bearer {your_jwt_token}
```

### **Body (JSON):**
```json
{
  "account_id": 1,
  "transaction_category_id": 2,
  "type": "expense",
  "amount": 50000,
  "date": "2025-09-23",
  "note": "Belanja groceries di Indomaret"
}
```

### **Contoh Response Success (201):**
```json
{
  "status": 201,
  "message": "Transaction created successfully",
  "data": {
    "id": 15,
    "user_id": 1,
    "account_id": 1,
    "transaction_category_id": 2,
    "type": "expense",
    "amount": "50000.00",
    "date": "2025-09-23T00:00:00.000000Z",
    "note": "Belanja groceries di Indomaret",
    "flag": "normal",
    "created_at": "2025-09-23T10:30:00.000000Z",
    "updated_at": "2025-09-23T10:30:00.000000Z",
    "account": {
      "id": 1,
      "name": "Dompet",
      "current_balance": "450000.00"
    },
    "category": {
      "id": 2,
      "name": "Makanan & Minuman",
      "type": "expense"
    }
  }
}
```

## 2. Transaksi Masuk (Income) - Gaji/Kiriman

### **Method:** POST
### **Endpoint:**
```
/user/transactions
```

### **Headers:**
```
Content-Type: application/json
Authorization: Bearer {your_jwt_token}
```

### **Body (JSON):**
```json
{
  "account_id": 1,
  "transaction_category_id": 5,
  "type": "income",
  "amount": 3000000,
  "date": "2025-09-23",
  "note": "Gaji bulan September 2025"
}
```

### **Contoh Response Success (201):**
```json
{
  "status": 201,
  "message": "Transaction created successfully",
  "data": {
    "id": 16,
    "user_id": 1,
    "account_id": 1,
    "transaction_category_id": 5,
    "type": "income",
    "amount": "3000000.00",
    "date": "2025-09-23T00:00:00.000000Z",
    "note": "Gaji bulan September 2025",
    "flag": "normal",
    "created_at": "2025-09-23T10:35:00.000000Z",
    "updated_at": "2025-09-23T10:35:00.000000Z",
    "account": {
      "id": 1,
      "name": "Dompet",
      "current_balance": "3450000.00"
    },
    "category": {
      "id": 5,
      "name": "Gaji",
      "type": "income"
    }
  }
}
```

## 3. Contoh Transaksi Dengan Tags (Opsional)

### **Body dengan Tags:**
```json
{
  "account_id": 2,
  "transaction_category_id": 3,
  "type": "expense",
  "amount": 25000,
  "date": "2025-09-23",
  "note": "Makan siang di warteg",
  "tags": [1, 3]
}
```

## Error Responses

### **Insufficient Balance (422):**
```json
{
  "status": 422,
  "message": "Insufficient balance for this transaction"
}
```

### **Validation Error (422):**
```json
{
  "account_id": ["The account id field is required."],
  "transaction_category_id": ["The transaction category id field is required."],
  "type": ["The type field is required."],
  "amount": ["The amount field is required."]
}
```

### **Account Not Found (422):**
```json
{
  "account_id": ["The selected account id is invalid."]
}
```

## Master Data yang Diperlukan

### **1. Buat Account Dulu (Rekening)**
```
POST /user/master-data/account
{
  "account_category_id": 1,
  "name": "Dompet",
  "initial_balance": 500000,
  "note": "Dompet cash harian"
}
```

### **2. Buat Transaction Categories**
```
POST /user/master-data/transaction-category
{
  "name": "Makanan & Minuman",
  "type": "expense"
}

POST /user/master-data/transaction-category
{
  "name": "Gaji",
  "type": "income"
}
```

## Auto-Update Balance

Setiap transaksi akan otomatis:
1. ✅ Update `account.current_balance`
2. ✅ Update `user.balance` (total semua rekening)
3. ✅ Validasi saldo cukup untuk expense
4. ✅ Lock account untuk prevent race condition

## Flow MVP Sederhana

1. **Setup Master Data** (Account + Categories)
2. **Tambah Transaksi Income** (Gaji, kiriman, dll)
3. **Tambah Transaksi Expense** (Belanja, makan, transport, dll)
4. **Saldo otomatis terupdate** tanpa manual calculation

## Tips Testing

- Gunakan `account_id` dan `transaction_category_id` yang valid
- Pastikan `type` match dengan category.type
- Amount harus > 0
- Date format: YYYY-MM-DD
- Expense akan dicek balance, income tidak