import { formatCurrency, formatDate } from '../../utils/formatters';

export default function RecentTransactions({ transactions }) {
  if (!transactions || transactions.length === 0) {
    return (
      <div className="recent-transactions">
        <h2>Transaksi Terakhir</h2>
        <div className="empty-state">
          <p>Belum ada transaksi</p>
        </div>
      </div>
    );
  }

  return (
    <div className="recent-transactions">
      <div className="header-row">
        <h2>Transaksi Terakhir</h2>
        <a href="/frontend/transactions" className="view-all">Lihat Semua</a>
      </div>
      <div className="transactions-list">
        {transactions.map((transaction) => (
          <div key={transaction.id} className={`transaction-item ${transaction.type}`}>
            <div className="transaction-info">
              <span className="transaction-date">{formatDate(transaction.date)}</span>
              <span className="transaction-desc">{transaction.description}</span>
            </div>
            <div className="transaction-amount">
              {formatCurrency(transaction.amount)}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
