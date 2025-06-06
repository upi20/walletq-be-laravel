import { useEffect, useState } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import { PlusIcon, ArrowUpIcon, ArrowDownIcon } from '@heroicons/react/24/outline';
import { BanknotesIcon, CreditCardIcon, WalletIcon } from '@heroicons/react/24/solid';
import { formatCurrency, formatDate } from '../utils/formatters';
import { Link } from 'react-router-dom';
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

export default function Dashboard() {
  const { user } = useAuth();
  const [loading, setLoading] = useState(true);
  const [dashboardData, setDashboardData] = useState({
    totalIncome: 0,
    totalExpense: 0,
    balance: 0,
    recentTransactions: [],
    accounts: [],
    chartData: []
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
          { id: 1, date: '2025-06-01', description: 'Gaji Bulanan', amount: 5000000, type: 'income', category: 'Gaji', account_name: 'Bank BCA' },
          { id: 2, date: '2025-06-02', description: 'Belanja Bulanan', amount: -1500000, type: 'expense', category: 'Belanja', account_name: 'Bank BCA' },
          { id: 3, date: '2025-06-03', description: 'Bayar Internet', amount: -350000, type: 'expense', category: 'Tagihan', account_name: 'Bank BCA' },
          { id: 4, date: '2025-06-04', description: 'Bensin', amount: -200000, type: 'expense', category: 'Transportasi', account_name: 'Gopay' },
          { id: 5, date: '2025-06-05', description: 'Makan Siang', amount: -100000, type: 'expense', category: 'Makanan', account_name: 'OVO' }
        ],
        accounts: [
          { id: 1, name: 'Kas', balance: 500000, category: 'Cash' },
          { id: 2, name: 'Bank BCA', balance: 2500000, category: 'Bank' },
          { id: 3, name: 'Gopay', balance: 150000, category: 'E-Wallet' },
          { id: 4, name: 'OVO', balance: 100000, category: 'E-Wallet' },
        ],
        chartData: [
          { name: 'Jan', income: 4500000, expense: 3000000 },
          { name: 'Feb', income: 4800000, expense: 3200000 },
          { name: 'Mar', income: 4300000, expense: 3800000 },
          { name: 'Apr', income: 5200000, expense: 3400000 },
          { name: 'May', income: 4700000, expense: 3100000 },
          { name: 'Jun', income: 5000000, expense: 3500000 },
        ]
      });
      setLoading(false);
    }, 1000);
  }, []);

  if (loading) {
    return (
      <div className="flex h-96 items-center justify-center">
        <div className="h-12 w-12 animate-spin rounded-full border-4 border-primary-500 border-t-transparent"></div>
      </div>
    );
  }

  // Custom tooltip for chart
  const CustomTooltip = ({ active, payload, label }) => {
    if (active && payload && payload.length) {
      return (
        <div className="rounded-md bg-white p-3 shadow-md">
          <p className="font-medium">{label}</p>
          <p className="text-primary-500">Pemasukan: {formatCurrency(payload[0].value)}</p>
          <p className="text-red-500">Pengeluaran: {formatCurrency(payload[1].value)}</p>
        </div>
      );
    }
    return null;
  };

  return (
    <div className="space-y-6">
      {/* Header & Welcome */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
          <p className="mt-1 text-sm text-gray-500">
            Halo, <span className="font-medium text-gray-700">{user?.name || 'User'}</span>! Selamat datang kembali.
          </p>
        </div>
        <Link
          to="/transactions"
          className="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
        >
          <PlusIcon className="-ml-0.5 mr-2 h-4 w-4" />
          Tambah Transaksi
        </Link>
      </div>

      {/* Financial Summary Cards */}
      <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        {/* Balance Card */}
        <div className="overflow-hidden rounded-lg bg-white shadow">
          <div className="p-5">
            <div className="flex items-center">
              <div className="flex-shrink-0">
                <BanknotesIcon className="h-10 w-10 rounded-full bg-primary-100 p-2 text-primary-600" />
              </div>
              <div className="ml-5 w-0 flex-1">
                <dl>
                  <dt className="text-sm font-medium text-gray-500 truncate">Total Saldo</dt>
                  <dd>
                    <div className="text-lg font-medium text-gray-900">
                      {formatCurrency(dashboardData.balance)}
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        {/* Income Card */}
        <div className="overflow-hidden rounded-lg bg-white shadow">
          <div className="p-5">
            <div className="flex items-center">
              <div className="flex-shrink-0">
                <ArrowUpIcon className="h-10 w-10 rounded-full bg-green-100 p-2 text-green-600" />
              </div>
              <div className="ml-5 w-0 flex-1">
                <dl>
                  <dt className="text-sm font-medium text-gray-500 truncate">Total Pemasukan</dt>
                  <dd>
                    <div className="text-lg font-medium text-gray-900">
                      {formatCurrency(dashboardData.totalIncome)}
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        {/* Expense Card */}
        <div className="overflow-hidden rounded-lg bg-white shadow">
          <div className="p-5">
            <div className="flex items-center">
              <div className="flex-shrink-0">
                <ArrowDownIcon className="h-10 w-10 rounded-full bg-red-100 p-2 text-red-600" />
              </div>
              <div className="ml-5 w-0 flex-1">
                <dl>
                  <dt className="text-sm font-medium text-gray-500 truncate">Total Pengeluaran</dt>
                  <dd>
                    <div className="text-lg font-medium text-gray-900">
                      {formatCurrency(Math.abs(dashboardData.totalExpense))}
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Chart and Transactions */}
      <div className="grid grid-cols-1 gap-5 lg:grid-cols-3">
        {/* Chart */}
        <div className="lg:col-span-2">
          <div className="h-full overflow-hidden rounded-lg bg-white shadow">
            <div className="p-5">
              <h3 className="text-lg font-medium leading-6 text-gray-900">Ringkasan Keuangan</h3>
              <div className="mt-2 h-64">
                <ResponsiveContainer width="100%" height="100%">
                  <AreaChart
                    data={dashboardData.chartData}
                    margin={{
                      top: 10,
                      right: 30,
                      left: 0,
                      bottom: 0,
                    }}
                  >
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="name" />
                    <YAxis tickFormatter={(value) => formatCurrency(value).replace('Rp', '')} />
                    <Tooltip content={<CustomTooltip />} />
                    <Area type="monotone" dataKey="income" stackId="1" stroke="#4ade80" fill="#4ade80" fillOpacity={0.6} />
                    <Area type="monotone" dataKey="expense" stackId="2" stroke="#f87171" fill="#f87171" fillOpacity={0.6} />
                  </AreaChart>
                </ResponsiveContainer>
              </div>
            </div>
          </div>
        </div>

        {/* Accounts Summary */}
        <div>
          <div className="h-full overflow-hidden rounded-lg bg-white shadow">
            <div className="p-5">
              <h3 className="text-lg font-medium leading-6 text-gray-900">Rekening</h3>
              <div className="mt-4 flow-root">
                <div className="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                  <div className="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <div className="space-y-3">
                      {dashboardData.accounts.map((account) => (
                        <div key={account.id} className="flex items-center justify-between rounded-lg border border-gray-100 bg-gray-50 p-3">
                          <div className="flex items-center">
                            <div className="mr-3 flex h-9 w-9 items-center justify-center rounded-full bg-primary-100">
                              {account.category === 'Cash' && <BanknotesIcon className="h-5 w-5 text-primary-600" />}
                              {account.category === 'Bank' && <CreditCardIcon className="h-5 w-5 text-primary-600" />}
                              {account.category === 'E-Wallet' && <WalletIcon className="h-5 w-5 text-primary-600" />}
                            </div>
                            <div>
                              <p className="text-sm font-medium text-gray-900">{account.name}</p>
                              <p className="text-xs text-gray-500">{account.category}</p>
                            </div>
                          </div>
                          <p className="font-semibold text-gray-900">{formatCurrency(account.balance)}</p>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              </div>
              <div className="mt-6">
                <Link
                  to="/accounts"
                  className="text-center block w-full rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                >
                  Lihat Semua Rekening
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Recent Transactions */}
      <div className="overflow-hidden rounded-lg bg-white shadow">
        <div className="p-5">
          <div className="flex items-center justify-between">
            <h3 className="text-lg font-medium leading-6 text-gray-900">Transaksi Terbaru</h3>
            <Link
              to="/transactions"
              className="text-sm font-medium text-primary-600 hover:text-primary-500"
            >
              Lihat Semua
            </Link>
          </div>
          <div className="mt-4 flow-root">
            <div className="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
              <div className="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Tanggal
                      </th>
                      <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Deskripsi
                      </th>
                      <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Kategori
                      </th>
                      <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Rekening
                      </th>
                      <th scope="col" className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                        Jumlah
                      </th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-200 bg-white">
                    {dashboardData.recentTransactions.map((transaction) => (
                      <tr key={transaction.id}>
                        <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                          {formatDate(transaction.date)}
                        </td>
                        <td className="px-6 py-4 text-sm text-gray-900">
                          {transaction.description}
                        </td>
                        <td className="px-6 py-4 text-sm text-gray-500">
                          <span className="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                            {transaction.category}
                          </span>
                        </td>
                        <td className="px-6 py-4 text-sm text-gray-500">
                          {transaction.account_name}
                        </td>
                        <td className={`whitespace-nowrap px-6 py-4 text-right text-sm font-medium ${
                          transaction.amount < 0 ? 'text-red-600' : 'text-green-600'
                        }`}>
                          {formatCurrency(transaction.amount)}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}