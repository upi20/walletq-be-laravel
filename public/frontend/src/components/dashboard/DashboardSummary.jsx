import { formatCurrency } from '../../utils/formatters';

export default function DashboardSummary({ totalIncome, totalExpense, balance }) {
  return (
    <div className="dashboard-summary">
      <div className="summary-card income">
        <h3>Total Pemasukan</h3>
        <p className="amount">{formatCurrency(totalIncome)}</p>
      </div>
      <div className="summary-card expense">
        <h3>Total Pengeluaran</h3>
        <p className="amount">{formatCurrency(totalExpense)}</p>
      </div>
      <div className="summary-card balance">
        <h3>Saldo</h3>
        <p className="amount">{formatCurrency(balance)}</p>
      </div>
    </div>
  );
}
