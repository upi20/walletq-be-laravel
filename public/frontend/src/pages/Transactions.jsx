import { useState, useEffect } from 'react';
import { PlusIcon, FunnelIcon, ArrowsUpDownIcon } from '@heroicons/react/24/outline';
import { transactionService } from '../services/api';
import { formatCurrency, formatDate } from '../utils/formatters';
import { toast } from 'react-hot-toast';

export default function Transactions() {
  const [loading, setLoading] = useState(true);
  const [transactions, setTransactions] = useState([]);
  const [showFilters, setShowFilters] = useState(false);
  const [filterParams, setFilterParams] = useState({
    start_date: '',
    end_date: '',
    type: '',
    category_id: '',
    account_id: '',
    sort_by: 'date',
    sort_direction: 'desc',
  });
  const [categories, setCategories] = useState([]);
  const [accounts, setAccounts] = useState([]);
  const [showForm, setShowForm] = useState(false);
  const [formData, setFormData] = useState({
    description: '',
    amount: '',
    type: 'expense',
    date: new Date().toISOString().slice(0, 10),
    account_id: '',
    category_id: '',
    notes: '',
  });

  useEffect(() => {
    fetchTransactions();
    fetchCategories();
    fetchAccounts();
  }, [filterParams]);

  const fetchTransactions = async () => {
    setLoading(true);
    try {
      const response = await transactionService.getAll(filterParams);
      setTransactions(response.data.data);
    } catch (error) {
      console.error('Failed to fetch transactions:', error);
      toast.error('Gagal mengambil data transaksi');
    } finally {
      setLoading(false);
    }
  };

  const fetchCategories = async () => {
    try {
      const response = await transactionService.getCategories();
      setCategories(response.data.data);
    } catch (error) {
      console.error('Failed to fetch categories:', error);
    }
  };

  const fetchAccounts = async () => {
    try {
      const response = await transactionService.getAccounts();
      setAccounts(response.data.data);
    } catch (error) {
      console.error('Failed to fetch accounts:', error);
    }
  };

  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    setFilterParams(prev => ({ ...prev, [name]: value }));
  };

  const handleFormChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Format amount based on transaction type
    const formattedData = {
      ...formData,
      amount: formData.type === 'expense' 
        ? -Math.abs(parseFloat(formData.amount)) 
        : Math.abs(parseFloat(formData.amount))
    };
    
    try {
      await transactionService.create(formattedData);
      toast.success('Transaksi berhasil ditambahkan');
      setShowForm(false);
      setFormData({
        description: '',
        amount: '',
        type: 'expense',
        date: new Date().toISOString().slice(0, 10),
        account_id: '',
        category_id: '',
        notes: '',
      });
      fetchTransactions();
    } catch (error) {
      console.error('Failed to create transaction:', error);
      toast.error('Gagal menambahkan transaksi');
    }
  };

  const handleDeleteTransaction = async (id) => {
    if (confirm('Anda yakin ingin menghapus transaksi ini?')) {
      try {
        await transactionService.delete(id);
        toast.success('Transaksi berhasil dihapus');
        fetchTransactions();
      } catch (error) {
        console.error('Failed to delete transaction:', error);
        toast.error('Gagal menghapus transaksi');
      }
    }
  };

  const handleSort = (field) => {
    setFilterParams(prev => ({
      ...prev,
      sort_by: field,
      sort_direction: prev.sort_by === field && prev.sort_direction === 'asc' ? 'desc' : 'asc'
    }));
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Transaksi</h1>
        <div className="flex space-x-2">
          <button
            onClick={() => setShowFilters(!showFilters)}
            className="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
          >
            <FunnelIcon className="-ml-1 mr-2 h-5 w-5 text-gray-500" />
            Filter
          </button>
          <button
            onClick={() => setShowForm(true)}
            className="inline-flex items-center rounded-md border border-transparent bg-primary-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
          >
            <PlusIcon className="-ml-1 mr-2 h-5 w-5" />
            Tambah Transaksi
          </button>
        </div>
      </div>

      {showFilters && (
        <div className="rounded-md bg-white p-4 shadow sm:p-6">
          <h2 className="mb-4 text-lg font-medium text-gray-900">Filter Transaksi</h2>
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            <div>
              <label htmlFor="start_date" className="block text-sm font-medium text-gray-700">
                Dari Tanggal
              </label>
              <input
                type="date"
                id="start_date"
                name="start_date"
                value={filterParams.start_date}
                onChange={handleFilterChange}
                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              />
            </div>
            <div>
              <label htmlFor="end_date" className="block text-sm font-medium text-gray-700">
                Sampai Tanggal
              </label>
              <input
                type="date"
                id="end_date"
                name="end_date"
                value={filterParams.end_date}
                onChange={handleFilterChange}
                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              />
            </div>
            <div>
              <label htmlFor="type" className="block text-sm font-medium text-gray-700">
                Jenis
              </label>
              <select
                id="type"
                name="type"
                value={filterParams.type}
                onChange={handleFilterChange}
                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              >
                <option value="">Semua</option>
                <option value="income">Pemasukan</option>
                <option value="expense">Pengeluaran</option>
              </select>
            </div>
            <div>
              <label htmlFor="category_id" className="block text-sm font-medium text-gray-700">
                Kategori
              </label>
              <select
                id="category_id"
                name="category_id"
                value={filterParams.category_id}
                onChange={handleFilterChange}
                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              >
                <option value="">Semua</option>
                {categories.map(category => (
                  <option key={category.id} value={category.id}>{category.name}</option>
                ))}
              </select>
            </div>
            <div>
              <label htmlFor="account_id" className="block text-sm font-medium text-gray-700">
                Rekening
              </label>
              <select
                id="account_id"
                name="account_id"
                value={filterParams.account_id}
                onChange={handleFilterChange}
                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              >
                <option value="">Semua</option>
                {accounts.map(account => (
                  <option key={account.id} value={account.id}>{account.name}</option>
                ))}
              </select>
            </div>
          </div>
        </div>
      )}

      {showForm && (
        <div className="fixed inset-0 z-50 overflow-y-auto">
          <div className="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div className="fixed inset-0 transition-opacity" aria-hidden="true">
              <div className="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span className="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
            <div className="inline-block transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6 sm:align-middle">
              <div className="absolute top-0 right-0 pt-4 pr-4">
                <button
                  type="button"
                  onClick={() => setShowForm(false)}
                  className="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                >
                  <span className="sr-only">Close</span>
                  <svg className="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              <div>
                <h3 className="text-lg font-medium leading-6 text-gray-900">Tambah Transaksi Baru</h3>
                <form onSubmit={handleSubmit} className="mt-4 space-y-4">
                  <div>
                    <label htmlFor="description" className="block text-sm font-medium text-gray-700">
                      Deskripsi
                    </label>
                    <input
                      type="text"
                      id="description"
                      name="description"
                      required
                      value={formData.description}
                      onChange={handleFormChange}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    />
                  </div>
                  <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                      <label htmlFor="amount" className="block text-sm font-medium text-gray-700">
                        Jumlah
                      </label>
                      <input
                        type="number"
                        id="amount"
                        name="amount"
                        min="0"
                        step="1000"
                        required
                        value={formData.amount}
                        onChange={handleFormChange}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                      />
                    </div>
                    <div>
                      <label htmlFor="type" className="block text-sm font-medium text-gray-700">
                        Jenis
                      </label>
                      <select
                        id="type"
                        name="type"
                        required
                        value={formData.type}
                        onChange={handleFormChange}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                      >
                        <option value="income">Pemasukan</option>
                        <option value="expense">Pengeluaran</option>
                      </select>
                    </div>
                  </div>
                  <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                      <label htmlFor="date" className="block text-sm font-medium text-gray-700">
                        Tanggal
                      </label>
                      <input
                        type="date"
                        id="date"
                        name="date"
                        required
                        value={formData.date}
                        onChange={handleFormChange}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                      />
                    </div>
                    <div>
                      <label htmlFor="account_id" className="block text-sm font-medium text-gray-700">
                        Rekening
                      </label>
                      <select
                        id="account_id"
                        name="account_id"
                        required
                        value={formData.account_id}
                        onChange={handleFormChange}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                      >
                        <option value="">Pilih Rekening</option>
                        {accounts.map(account => (
                          <option key={account.id} value={account.id}>{account.name}</option>
                        ))}
                      </select>
                    </div>
                  </div>
                  <div>
                    <label htmlFor="category_id" className="block text-sm font-medium text-gray-700">
                      Kategori
                    </label>
                    <select
                      id="category_id"
                      name="category_id"
                      required
                      value={formData.category_id}
                      onChange={handleFormChange}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >
                      <option value="">Pilih Kategori</option>
                      {categories
                        .filter(category => category.type === formData.type)
                        .map(category => (
                          <option key={category.id} value={category.id}>{category.name}</option>
                        ))
                      }
                    </select>
                  </div>
                  <div>
                    <label htmlFor="notes" className="block text-sm font-medium text-gray-700">
                      Catatan (Opsional)
                    </label>
                    <textarea
                      id="notes"
                      name="notes"
                      rows="3"
                      value={formData.notes}
                      onChange={handleFormChange}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    ></textarea>
                  </div>
                  <div className="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
                    <button
                      type="button"
                      onClick={() => setShowForm(false)}
                      className="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 sm:col-start-1 sm:mt-0 sm:text-sm"
                    >
                      Batal
                    </button>
                    <button
                      type="submit"
                      className="inline-flex w-full justify-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 sm:col-start-2 sm:text-sm"
                    >
                      Simpan
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      )}

      <div className="overflow-hidden rounded-lg bg-white shadow">
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th 
                  scope="col" 
                  className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                  onClick={() => handleSort('date')}
                >
                  <div className="flex items-center cursor-pointer">
                    Tanggal
                    <ArrowsUpDownIcon className="ml-1 h-4 w-4" />
                  </div>
                </th>
                <th 
                  scope="col" 
                  className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                  onClick={() => handleSort('description')}
                >
                  <div className="flex items-center cursor-pointer">
                    Deskripsi
                    <ArrowsUpDownIcon className="ml-1 h-4 w-4" />
                  </div>
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                  Kategori
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                  Rekening
                </th>
                <th 
                  scope="col" 
                  className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                  onClick={() => handleSort('amount')}
                >
                  <div className="flex items-center cursor-pointer">
                    Jumlah
                    <ArrowsUpDownIcon className="ml-1 h-4 w-4" />
                  </div>
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200 bg-white">
              {loading ? (
                <tr>
                  <td colSpan="6" className="px-6 py-4 text-center text-sm text-gray-500">
                    Loading...
                  </td>
                </tr>
              ) : transactions.length === 0 ? (
                <tr>
                  <td colSpan="6" className="px-6 py-4 text-center text-sm text-gray-500">
                    Tidak ada data transaksi
                  </td>
                </tr>
              ) : (
                transactions.map((transaction) => (
                  <tr key={transaction.id}>
                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                      {formatDate(transaction.date)}
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-900">
                      {transaction.description}
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-500">
                      {transaction.category?.name || '-'}
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-500">
                      {transaction.account?.name || '-'}
                    </td>
                    <td className={`whitespace-nowrap px-6 py-4 text-sm font-medium ${transaction.amount < 0 ? 'text-red-600' : 'text-green-600'}`}>
                      {formatCurrency(transaction.amount)}
                    </td>
                    <td className="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                      <button 
                        onClick={() => handleDeleteTransaction(transaction.id)}
                        className="text-red-600 hover:text-red-900"
                      >
                        Hapus
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
