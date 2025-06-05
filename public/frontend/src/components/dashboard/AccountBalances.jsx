import { formatCurrency } from '../../utils/formatters';

export default function AccountBalances({ accounts }) {
  if (!accounts || accounts.length === 0) {
    return (
      <div className="account-balances">
        <h2>Saldo Akun</h2>
        <div className="empty-state">
          <p>Belum ada akun</p>
        </div>
      </div>
    );
  }

  // Group accounts by category
  const groupedAccounts = accounts.reduce((acc, account) => {
    if (!acc[account.category]) {
      acc[account.category] = [];
    }
    acc[account.category].push(account);
    return acc;
  }, {});

  return (
    <div className="account-balances">
      <div className="header-row">
        <h2>Saldo Akun</h2>
        <a href="/frontend/accounts" className="view-all">Lihat Semua</a>
      </div>
      
      <div className="accounts-list">
        {Object.keys(groupedAccounts).map((category) => (
          <div key={category} className="account-category">
            <h3 className="category-name">{category}</h3>
            
            <div className="category-accounts">
              {groupedAccounts[category].map((account) => (
                <div key={account.id} className="account-item">
                  <div className="account-info">
                    <span className="account-name">{account.name}</span>
                  </div>
                  <div className="account-balance">
                    {formatCurrency(account.balance)}
                  </div>
                </div>
              ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
