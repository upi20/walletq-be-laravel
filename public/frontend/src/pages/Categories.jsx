import { useState, useEffect } from 'react';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/react/24/outline';
import { transactionCategoryService } from '../services/api';
import { toast } from 'react-hot-toast';

export default function Categories() {
  const [loading, setLoading] = useState(true);
  const [categories, setCategories] = useState([]);
  const [activeTab, setActiveTab] = useState('income');
  const [showForm, setShowForm] = useState(false);
  const [editMode, setEditMode] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    type: 'income',
    description: '',
  });

  useEffect(() => {
    fetchCategories();
  }, []);

  const fetchCategories = async () => {
    setLoading(true);
    try {
      const response = await transactionCategoryService.getAll();
      setCategories(response.data.data);
    } catch (error) {
      console.error('Failed to fetch categories:', error);
      toast.error('Gagal mengambil data kategori');
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
      type: activeTab,
      description: '',
    });
    setEditMode(false);
  };

  const handleAddCategory = () => {
    resetForm();
    setFormData(prev => ({ ...prev, type: activeTab }));
    setShowForm(true);
  };

  const handleEditCategory = (category) => {
    setFormData({
      id: category.id,
      name: category.name,
      type: category.type,
      description: category.description || '',
    });
    setActiveTab(category.type);
    setEditMode(true);
    setShowForm(true);
  };

  const handleDeleteCategory = async (id) => {
    if (confirm('Yakin ingin menghapus kategori ini? Semua transaksi terkait akan kehilangan kategori.')) {
      try {
        await transactionCategoryService.delete(id);
        toast.success('Kategori berhasil dihapus');
        fetchCategories();
      } catch (error) {
        console.error('Failed to delete category:', error);
        toast.error('Gagal menghapus kategori');
      }
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      if (editMode) {
        await transactionCategoryService.update(formData.id, formData);
        toast.success('Kategori berhasil diperbarui');
      } else {
        await transactionCategoryService.create(formData);
        toast.success('Kategori berhasil ditambahkan');
      }
      
      setShowForm(false);
      resetForm();
      fetchCategories();
    } catch (error) {
      console.error('Failed to save category:', error);
      toast.error(editMode ? 'Gagal memperbarui kategori' : 'Gagal menambahkan kategori');
    }
  };

  const filteredCategories = categories.filter(category => category.type === activeTab);

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Kategori Transaksi</h1>
        <button
          onClick={handleAddCategory}
          className="inline-flex items-center rounded-md border border-transparent bg-primary-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
        >
          <PlusIcon className="-ml-1 mr-2 h-5 w-5" />
          Tambah Kategori
        </button>
      </div>

      <div className="mb-4 border-b border-gray-200">
        <nav className="-mb-px flex" aria-label="Tabs">
          <button
            onClick={() => setActiveTab('income')}
            className={`${
              activeTab === 'income'
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
            } w-1/4 border-b-2 py-4 px-1 text-center text-sm font-medium`}
          >
            Pemasukan
          </button>
          <button
            onClick={() => setActiveTab('expense')}
            className={`${
              activeTab === 'expense'
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
            } w-1/4 border-b-2 py-4 px-1 text-center text-sm font-medium`}
          >
            Pengeluaran
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
                  {editMode ? 'Edit Kategori' : 'Tambah Kategori Baru'}
                </h3>
                <form onSubmit={handleSubmit} className="mt-4 space-y-4">
                  <div>
                    <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                      Nama Kategori
                    </label>
                    <input
                      type="text"
                      id="name"
                      name="name"
                      required
                      value={formData.name}
                      onChange={handleFormChange}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                      placeholder="Contoh: Gaji, Belanja, Transportasi"
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
                      placeholder="Deskripsi tambahan tentang kategori"
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
            <p className="mt-2 text-sm text-gray-500">Memuat data kategori...</p>
          </div>
        ) : filteredCategories.length === 0 ? (
          <div className="p-6 text-center">
            <p className="text-gray-500">Belum ada kategori {activeTab === 'income' ? 'pemasukan' : 'pengeluaran'}. Silakan tambahkan kategori baru.</p>
          </div>
        ) : (
          <ul className="divide-y divide-gray-200">
            {filteredCategories.map(category => (
              <li key={category.id} className="px-4 py-4 sm:px-6">
                <div className="flex items-center justify-between">
                  <div>
                    <h3 className="text-lg font-medium text-gray-900">{category.name}</h3>
                    {category.description && (
                      <p className="mt-1 max-w-2xl text-sm text-gray-500">{category.description}</p>
                    )}
                  </div>
                  <div className="flex space-x-3">
                    <button
                      onClick={() => handleEditCategory(category)}
                      className="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                    >
                      <PencilIcon className="-ml-1 mr-2 h-5 w-5 text-gray-500" />
                      Edit
                    </button>
                    <button
                      onClick={() => handleDeleteCategory(category.id)}
                      className="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-red-700 shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                    >
                      <TrashIcon className="-ml-1 mr-2 h-5 w-5 text-red-500" />
                      Hapus
                    </button>
                  </div>
                </div>
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  );
}
