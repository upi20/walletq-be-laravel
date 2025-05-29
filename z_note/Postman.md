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

## Account Management API

### List All Accounts
**Endpoint:** GET `/api/v1/user/master-data/account`

**Query Parameters:**
- `page` (integer, optional) - Page number for pagination (default: 1)
- `per_page` (integer, optional) - Number of items per page (default: 10, max: 50)
- `search` (string, optional) - Search accounts by name
- `account_category_id` (integer, optional) - Filter by account category
- `sort` (string, optional) - Sort field (options: name, balance, created_at)
- `order` (string, optional) - Sort order (asc or desc, default: asc)
- `min_balance` (numeric, optional) - Filter accounts with balance >= value
- `max_balance` (numeric, optional) - Filter accounts with balance <= value

**Example Requests:**
```
# Basic pagination
GET /api/v1/user/master-data/account?page=1&per_page=10

# Search with category filter
GET /api/v1/user/master-data/account?search=bank&account_category_id=1

# Sort by balance descending
GET /api/v1/user/master-data/account?sort=balance&order=desc

# Filter by balance range
GET /api/v1/user/master-data/account?min_balance=1000000&max_balance=5000000
```

**Headers:**
```json
{
    "Authorization": "Bearer {token}",
    "Accept": "application/json"
}
```

**Success Response (200 OK):**
```json
{
    "status": "success",
    "message": "Accounts retrieved successfully",
    "data": {
        "total_balance": 7500000,
        "accounts": {
            "current_page": 1,
            "data": [
                {
                    "id": 1,
                    "name": "Bank BCA",
                    "account_category_id": 1,
                    "initial_balance": 1000000,
                    "current_balance": 3500000,
                    "created_at": "2025-05-29T10:00:00.000000Z",
                    "updated_at": "2025-05-29T10:00:00.000000Z",
                    "category": {
                        "id": 1,
                        "name": "Bank Account",
                        "is_default": true
                    },
                    "transaction_count": 15,
                    "last_transaction_date": "2025-05-28T15:30:00.000000Z"
                }
            ],
            "first_page_url": "http://api.walletq.com/api/v1/user/master-data/account?page=1",
            "from": 1,
            "last_page": 3,
            "last_page_url": "http://api.walletq.com/api/v1/user/master-data/account?page=3",
            "links": [
                {
                    "url": null,
                    "label": "&laquo; Previous",
                    "active": false
                },
                {
                    "url": "http://api.walletq.com/api/v1/user/master-data/account?page=1",
                    "label": "1",
                    "active": true
                },
                {
                    "url": "http://api.walletq.com/api/v1/user/master-data/account?page=2",
                    "label": "2",
                    "active": false
                },
                {
                    "url": "http://api.walletq.com/api/v1/user/master-data/account?page=3",
                    "label": "3",
                    "active": false
                },
                {
                    "url": "http://api.walletq.com/api/v1/user/master-data/account?page=2",
                    "label": "Next &raquo;",
                    "active": false
                }
            ],
            "next_page_url": "http://api.walletq.com/api/v1/user/master-data/account?page=2",
            "path": "http://api.walletq.com/api/v1/user/master-data/account",
            "per_page": 10,
            "prev_page_url": null,
            "to": 10,
            "total": 25
        }
    },
    "meta": {
        "filters": {
            "search": null,
            "account_category_id": null,
            "min_balance": null,
            "max_balance": null
        },
        "sort": {
            "field": "name",
            "order": "asc"
        }
    }
}
```

**Error Responses:**

1. Invalid Query Parameters (422 Unprocessable Entity):
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "per_page": [
            "The per page must not be greater than 50."
        ],
        "order": [
            "The selected order is invalid."
        ]
    }
}
```

2. Invalid Sort Field (400 Bad Request):
```json
{
    "status": "error",
    "message": "Invalid sort field. Available fields: name, balance, created_at"
}
```

**Notes:**
- Total balance is calculated from all user accounts, not just the current page
- Transaction count and last transaction date are included for each account
- Results are paginated by default (10 items per page)
- Maximum items per page is 50 to prevent server overload
- Search is case-insensitive and matches partial account names
- Balance filters apply to current_balance
- Sort by balance uses current_balance field
- Response includes pagination metadata and applied filters

**Example cURL:**
```bash
# Basic list with pagination and sorting
curl -X GET "https://api.walletq.com/api/v1/user/master-data/account?page=1&per_page=10&sort=balance&order=desc" \
     -H "Authorization: Bearer {token}" \
     -H "Accept: application/json"

# Search and filter
curl -X GET "https://api.walletq.com/api/v1/user/master-data/account?search=bank&account_category_id=1&min_balance=1000000" \
     -H "Authorization: Bearer {token}" \
     -H "Accept: application/json"
```

## Account Category Management API

### List Account Categories
**Endpoint:** GET `/api/v1/user/master-data/account-category`

**Query Parameters:**
- `page` (integer, optional) - Page number for pagination (default: 1)
- `per_page` (integer, optional) - Number of items per page (default: 10, max: 50)
- `search` (string, optional) - Search categories by name
- `type` (string, optional) - Filter by type
- `is_default` (boolean, optional) - Filter default/custom categories
- `sort` (string, optional) - Sort field (options: name, created_at)
- `order` (string, optional) - Sort order (asc or desc, default: asc)

**Example Requests:**
```
# Basic pagination
GET /api/v1/user/master-data/account-category?page=1&per_page=10

# Search custom categories
GET /api/v1/user/master-data/account-category?search=bank&is_default=false

# Sort by name descending
GET /api/v1/user/master-data/account-category?sort=name&order=desc
```

**Headers:**
```json
{
    "Authorization": "Bearer {token}",
    "Accept": "application/json"
}
```

**Success Response (200 OK):**
```json
{
    "status": "success",
    "message": "Account categories retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "Bank Account",
                "type": "bank",
                "is_default": true,
                "created_at": "2025-05-29T10:00:00.000000Z",
                "updated_at": "2025-05-29T10:00:00.000000Z",
                "accounts_count": 3,
                "total_balance": 5000000
            },
            {
                "id": 2,
                "name": "E-Wallet",
                "type": "digital",
                "is_default": false,
                "created_at": "2025-05-29T10:00:00.000000Z",
                "updated_at": "2025-05-29T10:00:00.000000Z",
                "accounts_count": 2,
                "total_balance": 1500000
            }
        ],
        "first_page_url": "http://api.walletq.com/api/v1/user/master-data/account-category?page=1",
        "from": 1,
        "last_page": 2,
        "last_page_url": "http://api.walletq.com/api/v1/user/master-data/account-category?page=2",
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "active": false
            },
            {
                "url": "http://api.walletq.com/api/v1/user/master-data/account-category?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": "http://api.walletq.com/api/v1/user/master-data/account-category?page=2",
                "label": "2",
                "active": false
            },
            {
                "url": "http://api.walletq.com/api/v1/user/master-data/account-category?page=2",
                "label": "Next &raquo;",
                "active": false
            }
        ],
        "next_page_url": "http://api.walletq.com/api/v1/user/master-data/account-category?page=2",
        "path": "http://api.walletq.com/api/v1/user/master-data/account-category",
        "per_page": 10,
        "prev_page_url": null,
        "to": 10,
        "total": 15
    },
    "meta": {
        "filters": {
            "search": null,
            "type": null,
            "is_default": null
        },
        "sort": {
            "field": "name",
            "order": "asc"
        }
    }
}
```

### Create Account Category
**Endpoint:** POST `/api/v1/user/master-data/account-category`

**Headers:**
```json
{
    "Authorization": "Bearer {token}",
    "Accept": "application/json",
    "Content-Type": "application/json"
}
```

**Request Body:**
```json
{
    "name": "Investment Account",
    "type": "investment"
}
```

**Success Response (201 Created):**
```json
{
    "status": "success",
    "message": "Account category created successfully",
    "data": {
        "id": 3,
        "name": "Investment Account",
        "type": "investment",
        "is_default": false,
        "created_at": "2025-05-29T10:00:00.000000Z",
        "updated_at": "2025-05-29T10:00:00.000000Z"
    }
}
```

### View Account Category Details
**Endpoint:** GET `/api/v1/user/master-data/account-category/{id}`

**Headers:**
```json
{
    "Authorization": "Bearer {token}",
    "Accept": "application/json"
}
```

**Success Response (200 OK):**
```json
{
    "status": "success",
    "message": "Account category retrieved successfully",
    "data": {
        "id": 1,
        "name": "Bank Account",
        "type": "bank",
        "is_default": true,
        "created_at": "2025-05-29T10:00:00.000000Z",
        "updated_at": "2025-05-29T10:00:00.000000Z",
        "accounts": [
            {
                "id": 1,
                "name": "BCA Account",
                "current_balance": 3000000
            },
            {
                "id": 2,
                "name": "Mandiri Account",
                "current_balance": 2000000
            }
        ],
        "statistics": {
            "total_accounts": 2,
            "total_balance": 5000000,
            "average_balance": 2500000
        }
    }
}
```

### Update Account Category
**Endpoint:** PUT `/api/v1/user/master-data/account-category/{id}`

**Headers:**
```json
{
    "Authorization": "Bearer {token}",
    "Accept": "application/json",
    "Content-Type": "application/json"
}
```

**Request Body:**
```json
{
    "name": "Updated Investment Account",
    "type": "investment"
}
```

**Success Response (200 OK):**
```json
{
    "status": "success",
    "message": "Account category updated successfully",
    "data": {
        "id": 3,
        "name": "Updated Investment Account",
        "type": "investment",
        "is_default": false,
        "created_at": "2025-05-29T10:00:00.000000Z",
        "updated_at": "2025-05-29T10:00:00.000000Z"
    }
}
```

### Delete Account Category
**Endpoint:** DELETE `/api/v1/user/master-data/account-category/{id}`

**Headers:**
```json
{
    "Authorization": "Bearer {token}",
    "Accept": "application/json"
}
```

**Success Response (200 OK):**
```json
{
    "status": "success",
    "message": "Account category deleted successfully"
}
```

**Error Responses:**
1. Category Not Found (404 Not Found):
```json
{
    "status": "error",
    "message": "Account category not found"
}
```

2. Cannot Delete Default Category (403 Forbidden):
```json
{
    "status": "error",
    "message": "Cannot delete default category"
}
```

3. Category Has Accounts (422 Unprocessable Entity):
```json
{
    "status": "error",
    "message": "Cannot delete category with existing accounts"
}
```

4. Validation Error (422 Unprocessable Entity):
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "name": [
            "The name field is required."
        ],
        "type": [
            "The selected type is invalid."
        ]
    }
}
```

**Validation Rules:**
- name:
  - Required
  - String
  - Maximum 100 characters
  - Must be unique per user
  - No special characters except spaces and hyphens
- type:
  - Required
  - String
  - One of: bank, cash, digital, credit_card, investment, others

**Security Features:**
1. JWT Authentication required for all endpoints
2. Users can only access their own categories
3. Default categories are protected from modification
4. Rate limiting: 60 requests per minute
5. Input sanitization for XSS prevention
6. SQL injection protection via Laravel's query builder
7. Cannot delete categories with linked accounts

**Notes:**
- Default categories are created when user registers
- Each user has their own set of categories
- Categories with accounts cannot be deleted
- Default categories cannot be modified or deleted
- Names must be unique per user (case-insensitive)
- Response includes account statistics where relevant
- Soft deletes are used for data integrity

**Example cURL:**
```bash
# List categories with pagination and search
curl -X GET "https://api.walletq.com/api/v1/user/master-data/account-category?page=1&search=bank" \
     -H "Authorization: Bearer {token}" \
     -H "Accept: application/json"

# Create new category
curl -X POST "https://api.walletq.com/api/v1/user/master-data/account-category" \
     -H "Authorization: Bearer {token}" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{
         "name": "Investment Account",
         "type": "investment"
     }'
```
