## Folder Stucture
📦 UangKu API
│
├── 🔐 Auth
│   ├── POST  /auth/register     - Register user
│   ├── POST  /auth/login        - Login user
│   ├── GET   /auth/me           - Get current user
│   ├── POST  /auth/logout       - Logout user
│   └── POST  /auth/refresh      - Refresh JWT token
│
├── 💰 Transactions
│   ├── GET    /user/transactions           - List transactions
│   ├── POST   /user/transactions           - Create transaction
│   ├── GET    /user/transactions/{id}      - View transaction
│   ├── PUT    /user/transactions/{id}      - Update transaction
│   └── DELETE /user/transactions/{id}      - Delete transaction
│
├── 🔁 Transfers
│   ├── POST   /user/transfers              - Create transfer
│   └── GET    /user/transfers              - List transfers
│
├── 🧾 Debts
│   ├── GET    /user/debts                  - List debts
│   ├── POST   /user/debts                  - Create debt
│   ├── POST   /user/debts/{id}/pay         - Pay/cicil utang
│   └── GET    /user/debts/{id}/transactions - Get payment history
│
├── 🏷️ Tags
│   ├── GET    /user/tags                   - List tags
│   ├── POST   /user/tags                   - Create tag
│   └── DELETE /user/tags/{id}              - Delete tag
│
├── ⚙️ Settings
│   ├── GET    /user/settings               - Get all user settings
│   ├── PUT    /user/settings/{key}         - Update setting by key
│   └── POST   /user/settings               - Create/replace setting
│
└── 📦 Master Data
    ├── GET  /user/master-data/account-categories       - Default & custom account categories
    └── GET  /user/master-data/transaction-categories   - Default & custom transaction categories

## Environment
