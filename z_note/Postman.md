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
