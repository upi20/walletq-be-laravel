import { useEffect, useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import DashboardSummary from '../components/dashboard/DashboardSummary';
import RecentTransactions from '../components/dashboard/RecentTransactions';
import AccountBalances from '../components/dashboard/AccountBalances';

export default function Dashboard() {
  const { user } = useAuth();
  const [loading, setLoading] = useState(true);
  const [dashboardData, setDashboardData] = useState({
    totalIncome: 0,
    totalExpense: 0,
    balance: 0,
    recentTransactions: [],
    accounts: []
  });

  useEffect(() => {
    // Fetch dashboard data would go here
    // For now, we'll use placeholder data
    setTimeout(() => {
      setDashboardData({
        totalIncome: 5000000,
        totalExpense: 3500000,
        balance: 1500000,
        recentTransactions: [
          { id: 1, date: '2025-06-01', description: 'Gaji Bulanan', amount: 5000000, type: 'income' },
          { id: 2, date: '2025-06-02', description: 'Belanja Bulanan', amount: -1500000, type: 'expense' },
          { id: 3, date: '2025-06-03', description: 'Bayar Internet', amount: -350000, type: 'expense' },
          { id: 4, date: '2025-06-04', description: 'Bensin', amount: -200000, type: 'expense' },
          { id: 5, date: '2025-06-05', description: 'Makan Siang', amount: -100000, type: 'expense' }
        ],
        accounts: [
          { id: 1, name: 'Kas', balance: 500000, category: 'Cash' },
          { id: 2, name: 'Bank BCA', balance: 2500000, category: 'Bank' },
          { id: 3, name: 'Gopay', balance: 150000, category: 'E-Wallet' },
          { id: 4, name: 'OVO', balance: 100000, category: 'E-Wallet' },
        ]
      });
      setLoading(false);
    }, 1000);
  }, []);

  if (loading) {
    return <div className="loading-spinner">Loading...</div>;
  }

  return (
    <div className="dashboard-container">
      <div className="dashboard-header">
        <h1>Dashboard</h1>
        <p>Halo, {user?.name || 'User'}!</p>
      </div>

      <DashboardSummary 
        totalIncome={dashboardData.totalIncome}
        totalExpense={dashboardData.totalExpense}
        balance={dashboardData.balance}
      />

      <div className="dashboard-content">
        <div className="dashboard-left">
          <RecentTransactions transactions={dashboardData.recentTransactions} />
        </div>
        <div className="dashboard-right">
          <AccountBalances accounts={dashboardData.accounts} />
        </div>
      </div>
    </div>
  );
}
