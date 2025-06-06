import { useState, useEffect } from 'react';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/react/24/outline';
import { accountService, accountCategoryService } from '../services/api';
import { formatCurrency } from '../utils/formatters';
import { toast } from 'react-hot-toast';

export default function Accounts() {
  const [loading, setLoading] = useState(true);
  const [accounts, setAccounts] = useState([]);
  const [accountCategories, setAccountCategories] = useState([]);
  const [showForm, setShowForm] = useState(false);
  const [editMode, setEditMode] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    balance: '',
    account_category_id: '',
    description: '',
  });

  useEffect(() => {
    fetchAccounts();
    fetchAccountCategories();
  }, []);

  const fetchAccounts = async () => {
    setLoading(true);
    try {
      const response = await accountService.getAll();
      setAccounts(response.data.data);
    } catch (error) {
      console.error('Failed to fetch accounts:', error);
      toast.error('Gagal mengambil data rekening');
    } finally {
      setLoading(false);
    }
  };

  const fetchAccountCategories = async () => {
    try {
      const response = await accountCategoryService.getAll();
      setAccountCategories(response.data.data);
    } catch (error) {
      console.error('Failed to fetch account categories:', error);
      toast.error('Gagal mengambil data kategori rekening');
    }
  };

  const handleFormChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const resetForm = () => {
    setFormData({
      name: '',
      balance: '',
      account_category_id: '',
      description: '',
    });
    setEditMode(false);
  };

  const handleEditAccount = (account) => {
    setFormData({
      id: account.id,
      name: account.name,
      balance: Math.abs(account.balance),
      account_category_id: account.account_category_id,
      description: account.description || '',
    });
    setEditMode(true);
    setShowForm(true);
  };

  const handleDeleteAccount = async (id) => {
    if (confirm('Yakin ingin menghapus rekening ini? Semua transaksi terkait juga akan terhapus.')) {
      try {
        await accountService.delete(id);
        toast.success('Rekening berhasil dihapus');
        fetchAccounts();
      } catch (error) {
        console.error('Failed to delete account:', error);
        toast.error('Gagal menghapus rekening');
      }
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      if (editMode) {
        await accountService.update(formData.id, formData);
        toast.success('Rekening berhasil diperbarui');
      } else {
        await accountService.create(formData);
        toast.success('Rekening berhasil ditambahkan');
      }
      
      setShowForm(false);
      resetForm();
      fetchAccounts();
    } catch (error) {
      console.error('Failed to save account:', error);
      toast.error(editMode ? 'Gagal memperbarui rekening' : 'Gagal menambahkan rekening');
    }
  };

  const getCategoryName = (categoryId) => {
    const category = accountCategories.find(cat => cat.id === categoryId);
    return category ? category.name : '-';
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Rekening</h1>
        <button
          onClick={() => {
            resetForm();
            setShowForm(true);
          }}
          className="inline-flex items-center rounded-md border border-transparent bg-primary-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
        >
          <PlusIcon className="-ml-1 mr-2 h-5 w-5" />
          Tambah Rekening
        </button>
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
                  {editMode ? 'Edit Rekening' : 'Tambah Rekening Baru'}
                </h3>
                <form onSubmit={handleSubmit} className="mt-4 space-y-4">
                  <div>
                    <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                      Nama Rekening
                    </label>
                    <input
                      type="text"
                      id="name"
                      name="name"
                      required
                      value={formData.name}
                      onChange={handleFormChange}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                      placeholder="Contoh: BCA, Gopay, Dompet"
                    />
                  </div>
                  <div>
                    <label htmlFor="balance" className="block text-sm font-medium text-gray-700">
                      Saldo Awal
                    </label>
                    <input
                      type="number"
                      id="balance"
                      name="balance"
                      min="0"
                      step="1000"
                      required
                      value={formData.balance}
                      onChange={handleFormChange}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    />
                  </div>
                  <div>
                    <label htmlFor="account_category_id" className="block text-sm font-medium text-gray-700">
                      Kategori Rekening
                    </label>
                    <select
                      id="account_category_id"
                      name="account_category_id"
                      required
                      value={formData.account_category_id}
                      onChange={handleFormChange}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >
                      <option value="">Pilih Kategori</option>
                      {accountCategories.map(category => (
                        <option key={category.id} value={category.id}>{category.name}</option>
                      ))}
                    </select>
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
                      placeholder="Deskripsi tambahan tentang rekening"
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
            <p className="mt-2 text-sm text-gray-500">Memuat data rekening...</p>
          </div>
        ) : accounts.length === 0 ? (
          <div className="p-6 text-center">
            <p className="text-gray-500">Belum ada rekening. Silakan tambahkan rekening baru.</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3">
            {accounts.map(account => (
              <div key={account.id} className="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div className="px-4 py-5 sm:p-6">
                  <div className="flex items-center justify-between">
                    <h3 className="text-lg font-medium leading-6 text-gray-900">{account.name}</h3>
                    <span className="inline-flex items-center rounded-full bg-primary-100 px-2.5 py-0.5 text-xs font-medium text-primary-800">
                      {getCategoryName(account.account_category_id)}
                    </span>
                  </div>
                  <p className="mt-2 text-2xl font-semibold text-gray-900">{formatCurrency(account.balance)}</p>
                  {account.description && (
                    <p className="mt-2 text-sm text-gray-500">{account.description}</p>
                  )}
                  <div className="mt-4 flex justify-end space-x-3">
                    <button
                      onClick={() => handleEditAccount(account)}
                      className="inline-flex items-center rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                    >
                      <PencilIcon className="mr-1 h-4 w-4 text-gray-500" />
                      Edit
                    </button>
                    <button
                      onClick={() => handleDeleteAccount(account.id)}
                      className="inline-flex items-center rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-red-700 shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                    >
                      <TrashIcon className="mr-1 h-4 w-4 text-red-500" />
                      Hapus
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
