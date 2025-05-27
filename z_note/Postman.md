# Feature Concept
## User Auth
  - Register
  - Login
  - Forgot Password
  - Refresh JWT token

## User Profile
  - Get Profile
  - Edit Password
  - Edit Profile
    - Name
    - Email
    - Profile Picture

## Master Data
### Account Category
  - Create
  - Read
  - Update
  - Delete

### Transaction Category
  - Create
  - Read
    - Filter [type]
  - Update
  - Delete

### Account
  - Create
  - Read
  - Update
  - Delete

## Transaction
  - Create
  - Import Data
  - Template Import
  - Read
    - Filter
  - Delete

## Page
### Home
### Report
### Debt






# UangKu API
## 👤 Administrator
### 📦 Master Data 
#### Account Categories
[ ] - **GET**   `/admin/master-data/account-categories`               - List
[ ] - **POST**  `/admin/master-data/account-categories`               - Create
[ ] - **GET**   `/admin/master-data/account-categories/{id}`          - View
[ ] - **PUT**   `/admin/master-data/account-categories/{id}`          - Update
[ ] - **DELETE** `/admin/master-data/account-categories/{id}`         - Delete

#### Transaction Categories
[ ] - **GET**   `/admin/master-data/transaction-categories`               - List
[ ] - **POST**  `/admin/master-data/transaction-categories`               - Create
[ ] - **GET**   `/admin/master-data/transaction-categories/{id}`          - View
[ ] - **PUT**   `/admin/master-data/transaction-categories/{id}`          - Update
[ ] - **DELETE** `/admin/master-data/transaction-categories/{id}`         - Delete

### Users
[ ] - **GET**   `/admin/user`               - List
[ ] - **POST**  `/admin/user`               - Create
[ ] - **GET**   `/admin/user/{id}`          - View
[ ] - **PUT**   `/admin/user/{id}`          - Update
[ ] - **DELETE** `/admin/user/{id}`         - Delete

## 👤 User 
### 🔐 Auth
[ ] - **POST**  `/auth/login`         - Login user
[ ] - **POST**  `/auth/register`      - Register user
[ ] - **GET**   `/auth/me`            - Get current user
[ ] - **POST**  `/auth/logout`        - Logout user
[ ] - **POST**  `/auth/refresh`       - Refresh JWT token

### 💰 Transactions
[ ] - **GET**   `/user/transactions`               - List transactions
[ ] - **POST**  `/user/transactions`               - Create transaction
[ ] - **POST**  `/user/transactions/bulk`          - Create multiple transactions at once
[ ] - **GET**   `/user/transactions/{id}`          - View transaction
[ ] - **PUT**   `/user/transactions/{id}`          - Update transaction
[ ] - **DELETE** `/user/transactions/{id}`         - Delete transaction

### 🔁 Transfers
[ ] - **POST**  `/user/transfers`                  - Create transfer
[ ] - **GET**   `/user/transfers`                  - List transfers

### 🧾 Debts
[ ] - **GET**   `/user/debts`                      - List debts
[ ] - **POST**  `/user/debts`                      - Create debt
[ ] - **POST**  `/user/debts/{id}/pay`             - Pay/cicil utang
[ ] - **GET**   `/user/debts/{id}/transactions`    - Get payment history

### 🏷️ Tags
[ ] - **GET**   `/user/tags`                       - List tags
[ ] - **POST**  `/user/tags`                       - Create tag
[ ] - **DELETE** `/user/tags/{id}`                 - Delete tag

### ⚙️ Settings
[ ] - **GET**   `/user/settings`                   - Get all user settings
[ ] - **PUT**   `/user/settings/{key}`             - Update setting by key
[ ] - **POST**  `/user/settings`                   - Create/replace setting

### 📦 Master Data
[ ] - **GET**   `/user/master-data/account-categories`  - Default & custom account categories
[ ] - **GET**   `/user/master-data/transaction-categories` - Default & custom transaction categories


### Environment

# API Documentation with Examples

## Transactions API

### Create Single Transaction
**Endpoint:** POST `/user/transactions`

**Request Body:**
```json
{
    "account_id": 1,
    "transaction_category_id": 2,
    "type": "expense",  // income or expense
    "amount": 50000,
    "date": "2025-05-28",
    "note": "Lunch at restaurant",
    "tags": ["food", "lunch"]  // optional
}
```

### Create Bulk Transactions
**Endpoint:** POST `/user/transactions/bulk`

**Request Body:**
```json
{
    "transactions": [
        {
            "account_id": 1,
            "transaction_category_id": 2,
            "type": "expense",
            "amount": 50000,
            "date": "2025-05-28",
            "note": "Lunch",
            "tags": ["food"]
        },
        {
            "account_id": 1,
            "transaction_category_id": 3,
            "type": "expense",
            "amount": 25000,
            "date": "2025-05-28",
            "note": "Transport",
            "tags": ["transport"]
        }
    ]
}
```

### Create Transfer
**Endpoint:** POST `/user/transfers`

**Request Body:**
```json
{
    "from_account_id": 1,
    "to_account_id": 2,
    "amount": 100000,
    "date": "2025-05-28",
    "note": "Transfer to savings"
}
```

### Create Debt
**Endpoint:** POST `/user/debts`

**Request Body:**
```json
{
    "type": "debt",  // debt or receivable
    "amount": 500000,
    "date": "2025-05-28",
    "due_date": "2025-06-28",
    "note": "Borrowed money from John",
    "contact_name": "John Doe"
}
```

### Pay Debt
**Endpoint:** POST `/user/debts/{id}/pay`

**Request Body:**
```json
{
    "amount": 100000,
    "date": "2025-05-28",
    "note": "First payment"
}
```

## Transaction Categories API

### List Transaction Categories
**Endpoint:** GET `/user/master-data/transaction-category`

Query Parameters:
- type (optional) - Filter by type: 'income' or 'expense'

### Create Transaction Category
**Endpoint:** POST `/user/master-data/transaction-category`

**Request Body:**
```json
{
    "name": "Food & Beverages",
    "type": "expense"  // income or expense
}
```

### View Transaction Category
**Endpoint:** GET `/user/master-data/transaction-category/{id}`

### Update Transaction Category
**Endpoint:** PUT `/user/master-data/transaction-category/{id}`

**Request Body:**
```json
{
    "name": "Updated Category Name",
    "type": "expense"  // income or expense
}
```

### Delete Transaction Category
**Endpoint:** DELETE `/user/master-data/transaction-category/{id}`

Notes:
- Default categories cannot be updated or deleted
- Categories that are being used in transactions cannot be deleted
- The type field must be either 'income' or 'expense'
