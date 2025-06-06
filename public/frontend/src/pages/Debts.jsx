import { useState, useEffect } from 'react';
import { PlusIcon, ArrowTrendingUpIcon, ArrowTrendingDownIcon } from '@heroicons/react/24/outline';
import { formatCurrency, formatDate } from '../utils/formatters';
import { toast } from 'react-hot-toast';
import api from '../services/api';

export default function Debts() {
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('debts');
  const [items, setItems] = useState([]);
  const [showForm, setShowForm] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    amount: '',
    description: '',
    due_date: '',
    status: 'unpaid',
    type: 'debt' // debt or receivable
  });

  useEffect(() => {
    fetchItems();
  }, [activeTab]);

  const fetchItems = async () => {
    setLoading(true);
    try {
      const type = activeTab === 'debts' ? 'debt' : 'receivable';
      const response = await api.get(`/user/debts?type=${type}`);
      setItems(response.data.data);
    } catch (error) {
      console.error(`Failed to fetch ${activeTab}:`, error);
      toast.error(`Gagal mengambil data ${activeTab === 'debts' ? 'utang' : 'piutang'}`);
    } finally {
      setLoading(false);
    }
  };

  const handleFormChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const resetForm = () => {
    setFormData({
      name: '',
      amount: '',
      description: '',
      due_date: '',
      status: 'unpaid',
      type: activeTab === 'debts' ? 'debt' : 'receivable'
    });
  };

  const handleAddItem = () => {
    resetForm();
    setFormData(prev => ({
      ...prev,
      type: activeTab === 'debts' ? 'debt' : 'receivable'
    }));
    setShowForm(true);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      await api.post('/user/debts', formData);
      toast.success(`${activeTab === 'debts' ? 'Utang' : 'Piutang'} berhasil ditambahkan`);
      setShowForm(false);
      resetForm();
      fetchItems();
    } catch (error) {
      console.error('Failed to save item:', error);
      toast.error(`Gagal menambahkan ${activeTab === 'debts' ? 'utang' : 'piutang'}`);
    }
  };

  const handleStatusToggle = async (id) => {
    try {
      const item = items.find(i => i.id === id);
      const newStatus = item.status === 'paid' ? 'unpaid' : 'paid';
      
      await api.put(`/user/debts/${id}`, { status: newStatus });
      toast.success(`Status berhasil diubah menjadi ${newStatus === 'paid' ? 'Lunas' : 'Belum Lunas'}`);
      fetchItems();
    } catch (error) {
      console.error('Failed to update status:', error);
      toast.error('Gagal mengubah status');
    }
  };

  const handleDeleteItem = async (id) => {
    if (confirm('Yakin ingin menghapus data ini?')) {
      try {
        await api.delete(`/user/debts/${id}`);
        toast.success(`${activeTab === 'debts' ? 'Utang' : 'Piutang'} berhasil dihapus`);
        fetchItems();
      } catch (error) {
        console.error('Failed to delete item:', error);
        toast.error(`Gagal menghapus ${activeTab === 'debts' ? 'utang' : 'piutang'}`);
      }
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Utang & Piutang</h1>
        <button
          onClick={handleAddItem}
          className="inline-flex items-center rounded-md border border-transparent bg-primary-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
        >
          <PlusIcon className="-ml-1 mr-2 h-5 w-5" />
          Tambah {activeTab === 'debts' ? 'Utang' : 'Piutang'}
        </button>
      </div>

      <div className="mb-4 border-b border-gray-200">
        <nav className="-mb-px flex" aria-label="Tabs">
          <button
            onClick={() => setActiveTab('debts')}
            className={`${
              activeTab === 'debts'
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
            } w-1/4 border-b-2 py-4 px-1 text-center text-sm font-medium flex justify-center items-center`}
          >
            <ArrowTrendingDownIcon className="mr-2 h-5 w-5" />
            Utang
          </button>
          <button
            onClick={() => setActiveTab('receivables')}
            className={`${
              activeTab === 'receivables'
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
            } w-1/4 border-b-2 py-4 px-1 text-center text-sm font-medium flex justify-center items-center`}
          >
            <ArrowTrendingUpIcon className="mr-2 h-5 w-5" />
            Piutang
          </button>
        </nav>
      </div>

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
                <h3 className="text-lg font-medium leading-6 text-gray-900">
                  Tambah {activeTab === 'debts' ? 'Utang' : 'Piutang'} Baru
                </h3>
                <form onSubmit={handleSubmit} className="mt-4 space-y-4">
                  <div>
                    <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                      Nama {activeTab === 'debts' ? 'Pemberi Utang' : 'Peminjam'}
                    </label>
                    <input
                      type="text"
                      id="name"
                      name="name"
                      required
                      value={formData.name}
                      onChange={handleFormChange}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    />
                  </div>
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
                    <label htmlFor="due_date" className="block text-sm font-medium text-gray-700">
                      Tanggal Jatuh Tempo
                    </label>
                    <input
                      type="date"
                      id="due_date"
                      name="due_date"
                      required
                      value={formData.due_date}
                      onChange={handleFormChange}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    />
                  </div>
                  <div>
                    <label htmlFor="description" className="block text-sm font-medium text-gray-700">
                      Deskripsi (Opsional)
                    </label>
                    <textarea
                      id="description"
                      name="description"
                      rows="3"
                      value={formData.description}
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
        {loading ? (
          <div className="p-6 text-center">
            <div className="mx-auto h-8 w-8 animate-spin rounded-full border-4 border-primary-500 border-t-transparent"></div>
            <p className="mt-2 text-sm text-gray-500">
              Memuat data {activeTab === 'debts' ? 'utang' : 'piutang'}...
            </p>
          </div>
        ) : items.length === 0 ? (
          <div className="p-6 text-center">
            <p className="text-gray-500">
              Belum ada {activeTab === 'debts' ? 'utang' : 'piutang'}. Silakan tambahkan baru.
            </p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    Nama
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    Jumlah
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    Tanggal Jatuh Tempo
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    Status
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    Aksi
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200 bg-white">
                {items.map((item) => (
                  <tr key={item.id}>
                    <td className="whitespace-nowrap px-6 py-4">
                      <div className="text-sm font-medium text-gray-900">{item.name}</div>
                      {item.description && (
                        <div className="text-sm text-gray-500">{item.description}</div>
                      )}
                    </td>
                    <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                      {formatCurrency(item.amount)}
                    </td>
                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {formatDate(item.due_date)}
                    </td>
                    <td className="whitespace-nowrap px-6 py-4 text-sm">
                      <span className={`inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${
                        item.status === 'paid' 
                          ? 'bg-green-100 text-green-800' 
                          : 'bg-yellow-100 text-yellow-800'
                      }`}>
                        {item.status === 'paid' ? 'Lunas' : 'Belum Lunas'}
                      </span>
                    </td>
                    <td className="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                      <button
                        onClick={() => handleStatusToggle(item.id)}
                        className="mr-3 text-primary-600 hover:text-primary-900"
                      >
                        Ubah Status
                      </button>
                      <button
                        onClick={() => handleDeleteItem(item.id)}
                        className="text-red-600 hover:text-red-900"
                      >
                        Hapus
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
