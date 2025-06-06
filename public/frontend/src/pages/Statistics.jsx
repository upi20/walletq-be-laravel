import { useState, useEffect } from 'react';
import {
  AreaChart,
  Area,
  BarChart,
  Bar,
  PieChart,
  Pie,
  Cell,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from 'recharts';
import { formatCurrency } from '../utils/formatters';
import { toast } from 'react-hot-toast';
import api from '../services/api';

export default function Statistics() {
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('monthly');
  const [monthlyData, setMonthlyData] = useState([]);
  const [categoryData, setCategoryData] = useState([]);
  const [accountData, setAccountData] = useState([]);
  const [dateRange, setDateRange] = useState({
    start_date: new Date(new Date().getFullYear(), new Date().getMonth() - 5, 1).toISOString().slice(0, 10),
    end_date: new Date().toISOString().slice(0, 10),
  });

  const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884d8', '#82ca9d', '#ffc658', '#8dd1e1'];

  useEffect(() => {
    fetchStatistics();
  }, [dateRange]);

  const fetchStatistics = async () => {
    setLoading(true);
    try {
      const response = await api.get('/user/statistics', { params: dateRange });
      setMonthlyData(response.data.monthly_data);
      setCategoryData(response.data.category_data);
      setAccountData(response.data.account_data);
    } catch (error) {
      console.error('Failed to fetch statistics:', error);
      toast.error('Gagal mengambil data statistik');
    } finally {
      setLoading(false);
    }
  };

  const handleDateRangeChange = (e) => {
    const { name, value } = e.target;
    setDateRange(prev => ({ ...prev, [name]: value }));
  };

  const renderCustomizedLabel = ({ cx, cy, midAngle, innerRadius, outerRadius, percent, index, name }) => {
    const RADIAN = Math.PI / 180;
    const radius = innerRadius + (outerRadius - innerRadius) * 0.5;
    const x = cx + radius * Math.cos(-midAngle * RADIAN);
    const y = cy + radius * Math.sin(-midAngle * RADIAN);

    return (
      <text
        x={x}
        y={y}
        fill="white"
        textAnchor={x > cx ? 'start' : 'end'}
        dominantBaseline="central"
      >
        {`${name}: ${(percent * 100).toFixed(0)}%`}
      </text>
    );
  };

  const CustomTooltip = ({ active, payload, label }) => {
    if (active && payload && payload.length) {
      return (
        <div className="rounded-md bg-white p-3 shadow-md">
          <p className="font-medium">{label}</p>
          {payload.map((entry, index) => (
            <p key={`item-${index}`} style={{ color: entry.color }}>
              {entry.name}: {formatCurrency(entry.value)}
            </p>
          ))}
        </div>
      );
    }
    return null;
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Statistik</h1>
        <div className="flex space-x-3">
          <div>
            <label htmlFor="start_date" className="block text-sm font-medium text-gray-700">
              Dari
            </label>
            <input
              type="date"
              id="start_date"
              name="start_date"
              value={dateRange.start_date}
              onChange={handleDateRangeChange}
              className="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            />
          </div>
          <div>
            <label htmlFor="end_date" className="block text-sm font-medium text-gray-700">
              Sampai
            </label>
            <input
              type="date"
              id="end_date"
              name="end_date"
              value={dateRange.end_date}
              onChange={handleDateRangeChange}
              className="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            />
          </div>
        </div>
      </div>

      <div className="mb-4 border-b border-gray-200">
        <nav className="-mb-px flex" aria-label="Tabs">
          <button
            onClick={() => setActiveTab('monthly')}
            className={`${
              activeTab === 'monthly'
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
            } w-1/4 border-b-2 py-4 px-1 text-center text-sm font-medium`}
          >
            Bulanan
          </button>
          <button
            onClick={() => setActiveTab('category')}
            className={`${
              activeTab === 'category'
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
            } w-1/4 border-b-2 py-4 px-1 text-center text-sm font-medium`}
          >
            Kategori
          </button>
          <button
            onClick={() => setActiveTab('accounts')}
            className={`${
              activeTab === 'accounts'
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
            } w-1/4 border-b-2 py-4 px-1 text-center text-sm font-medium`}
          >
            Rekening
          </button>
        </nav>
      </div>

      <div className="overflow-hidden rounded-lg bg-white shadow">
        {loading ? (
          <div className="flex h-80 items-center justify-center">
            <div className="h-10 w-10 animate-spin rounded-full border-4 border-primary-500 border-t-transparent"></div>
            <p className="ml-3 text-sm font-medium text-gray-500">Memuat data statistik...</p>
          </div>
        ) : (
          <div className="p-4">
            {activeTab === 'monthly' && (
              <div className="h-80">
                <h3 className="mb-4 text-lg font-medium text-gray-900">Pendapatan & Pengeluaran Bulanan</h3>
                <ResponsiveContainer width="100%" height="100%">
                  <AreaChart
                    data={monthlyData}
                    margin={{
                      top: 5,
                      right: 30,
                      left: 20,
                      bottom: 5,
                    }}
                  >
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="month" />
                    <YAxis tickFormatter={(value) => formatCurrency(value).replace('Rp', '')} />
                    <Tooltip content={<CustomTooltip />} />
                    <Legend />
                    <Area 
                      type="monotone" 
                      dataKey="income" 
                      name="Pendapatan" 
                      stackId="1" 
                      stroke="#00C49F" 
                      fill="#00C49F" 
                      fillOpacity={0.6}
                    />
                    <Area 
                      type="monotone" 
                      dataKey="expense" 
                      name="Pengeluaran" 
                      stackId="2" 
                      stroke="#FF8042" 
                      fill="#FF8042"
                      fillOpacity={0.6} 
                    />
                  </AreaChart>
                </ResponsiveContainer>
              </div>
            )}

            {activeTab === 'category' && (
              <div className="grid gap-6 md:grid-cols-2">
                <div className="h-80">
                  <h3 className="mb-4 text-lg font-medium text-gray-900">Pengeluaran per Kategori</h3>
                  <ResponsiveContainer width="100%" height="100%">
                    <PieChart>
                      <Pie
                        data={categoryData.expense}
                        cx="50%"
                        cy="50%"
                        labelLine={false}
                        label={renderCustomizedLabel}
                        outerRadius={80}
                        fill="#8884d8"
                        dataKey="value"
                        nameKey="name"
                      >
                        {categoryData.expense.map((entry, index) => (
                          <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                        ))}
                      </Pie>
                      <Tooltip formatter={(value) => formatCurrency(value)} />
                      <Legend />
                    </PieChart>
                  </ResponsiveContainer>
                </div>
                <div className="h-80">
                  <h3 className="mb-4 text-lg font-medium text-gray-900">Pendapatan per Kategori</h3>
                  <ResponsiveContainer width="100%" height="100%">
                    <PieChart>
                      <Pie
                        data={categoryData.income}
                        cx="50%"
                        cy="50%"
                        labelLine={false}
                        label={renderCustomizedLabel}
                        outerRadius={80}
                        fill="#8884d8"
                        dataKey="value"
                        nameKey="name"
                      >
                        {categoryData.income.map((entry, index) => (
                          <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                        ))}
                      </Pie>
                      <Tooltip formatter={(value) => formatCurrency(value)} />
                      <Legend />
                    </PieChart>
                  </ResponsiveContainer>
                </div>
              </div>
            )}

            {activeTab === 'accounts' && (
              <div className="h-80">
                <h3 className="mb-4 text-lg font-medium text-gray-900">Saldo per Rekening</h3>
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart
                    data={accountData}
                    margin={{
                      top: 5,
                      right: 30,
                      left: 20,
                      bottom: 5,
                    }}
                  >
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="name" />
                    <YAxis tickFormatter={(value) => formatCurrency(value).replace('Rp', '')} />
                    <Tooltip formatter={(value) => formatCurrency(value)} />
                    <Legend />
                    <Bar dataKey="value" name="Saldo">
                      {accountData.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                      ))}
                    </Bar>
                  </BarChart>
                </ResponsiveContainer>
              </div>
            )}
          </div>
        )}
      </div>

      {/* Additional statistics blocks could go here */}
    </div>
  );
}
