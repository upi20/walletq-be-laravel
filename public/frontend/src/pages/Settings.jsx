import { useState, useEffect } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import { toast } from 'react-hot-toast';
import api from '../services/api';

export default function Settings() {
  const { user, logout } = useAuth();
  const [loading, setLoading] = useState(true);
  const [userFormData, setUserFormData] = useState({
    name: '',
    email: '',
  });
  const [passwordFormData, setPasswordFormData] = useState({
    current_password: '',
    password: '',
    password_confirmation: '',
  });
  const [settings, setSettings] = useState({
    currency: 'IDR',
    date_format: 'DD/MM/YYYY',
    theme: 'light',
  });

  useEffect(() => {
    if (user) {
      setUserFormData({
        name: user.name || '',
        email: user.email || '',
      });
    }
    fetchSettings();
  }, [user]);

  const fetchSettings = async () => {
    setLoading(true);
    try {
      const response = await api.get('/user/settings');
      if (response.data.data) {
        setSettings(response.data.data);
      }
    } catch (error) {
      console.error('Failed to fetch settings:', error);
      toast.error('Gagal mengambil pengaturan');
    } finally {
      setLoading(false);
    }
  };

  const handleUserFormChange = (e) => {
    const { name, value } = e.target;
    setUserFormData(prev => ({ ...prev, [name]: value }));
  };

  const handlePasswordFormChange = (e) => {
    const { name, value } = e.target;
    setPasswordFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSettingsChange = (e) => {
    const { name, value } = e.target;
    setSettings(prev => ({ ...prev, [name]: value }));
  };

  const updateProfile = async (e) => {
    e.preventDefault();
    try {
      await api.put('/user/profile', userFormData);
      toast.success('Profil berhasil diperbarui');
    } catch (error) {
      console.error('Failed to update profile:', error);
      toast.error('Gagal memperbarui profil');
    }
  };

  const changePassword = async (e) => {
    e.preventDefault();
    try {
      await api.put('/user/password', passwordFormData);
      toast.success('Kata sandi berhasil diperbarui');
      setPasswordFormData({
        current_password: '',
        password: '',
        password_confirmation: '',
      });
    } catch (error) {
      console.error('Failed to change password:', error);
      toast.error('Gagal memperbarui kata sandi');
    }
  };

  const saveSettings = async (e) => {
    e.preventDefault();
    try {
      await api.put('/user/settings', settings);
      toast.success('Pengaturan berhasil disimpan');
    } catch (error) {
      console.error('Failed to save settings:', error);
      toast.error('Gagal menyimpan pengaturan');
    }
  };

  const exportData = async (format) => {
    try {
      const response = await api.get(`/user/export?format=${format}`, {
        responseType: 'blob',
      });
      
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `walletq-export.${format}`);
      document.body.appendChild(link);
      link.click();
      link.remove();
      
      toast.success(`Data berhasil diekspor ke format ${format.toUpperCase()}`);
    } catch (error) {
      console.error('Failed to export data:', error);
      toast.error('Gagal mengekspor data');
    }
  };

  const importData = async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const file = formData.get('import_file');
    
    if (!file) {
      toast.error('Silakan pilih file terlebih dahulu');
      return;
    }
    
    try {
      const formData = new FormData();
      formData.append('file', file);
      
      await api.post('/user/import', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      
      toast.success('Data berhasil diimpor');
    } catch (error) {
      console.error('Failed to import data:', error);
      toast.error('Gagal mengimpor data');
    }
  };

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-900">Pengaturan</h1>

      <div className="overflow-hidden rounded-lg bg-white shadow">
        <div className="px-4 py-5 sm:p-6">
          <h2 className="text-lg font-medium leading-6 text-gray-900">Profil Pengguna</h2>
          <div className="mt-4">
            <form onSubmit={updateProfile} className="space-y-4">
              <div>
                <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                  Nama
                </label>
                <input
                  type="text"
                  name="name"
                  id="name"
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  value={userFormData.name}
                  onChange={handleUserFormChange}
                />
              </div>
              <div>
                <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                  Email
                </label>
                <input
                  type="email"
                  name="email"
                  id="email"
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  value={userFormData.email}
                  onChange={handleUserFormChange}
                />
              </div>
              <div>
                <button
                  type="submit"
                  className="inline-flex justify-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                >
                  Simpan Profil
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div className="overflow-hidden rounded-lg bg-white shadow">
        <div className="px-4 py-5 sm:p-6">
          <h2 className="text-lg font-medium leading-6 text-gray-900">Ubah Kata Sandi</h2>
          <div className="mt-4">
            <form onSubmit={changePassword} className="space-y-4">
              <div>
                <label htmlFor="current_password" className="block text-sm font-medium text-gray-700">
                  Kata Sandi Saat Ini
                </label>
                <input
                  type="password"
                  name="current_password"
                  id="current_password"
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  value={passwordFormData.current_password}
                  onChange={handlePasswordFormChange}
                />
              </div>
              <div>
                <label htmlFor="password" className="block text-sm font-medium text-gray-700">
                  Kata Sandi Baru
                </label>
                <input
                  type="password"
                  name="password"
                  id="password"
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  value={passwordFormData.password}
                  onChange={handlePasswordFormChange}
                />
              </div>
              <div>
                <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700">
                  Konfirmasi Kata Sandi Baru
                </label>
                <input
                  type="password"
                  name="password_confirmation"
                  id="password_confirmation"
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  value={passwordFormData.password_confirmation}
                  onChange={handlePasswordFormChange}
                />
              </div>
              <div>
                <button
                  type="submit"
                  className="inline-flex justify-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                >
                  Ubah Kata Sandi
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div className="overflow-hidden rounded-lg bg-white shadow">
        <div className="px-4 py-5 sm:p-6">
          <h2 className="text-lg font-medium leading-6 text-gray-900">Pengaturan Aplikasi</h2>
          <div className="mt-4">
            <form onSubmit={saveSettings} className="space-y-4">
              <div>
                <label htmlFor="currency" className="block text-sm font-medium text-gray-700">
                  Mata Uang
                </label>
                <select
                  id="currency"
                  name="currency"
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  value={settings.currency}
                  onChange={handleSettingsChange}
                >
                  <option value="IDR">Rupiah (IDR)</option>
                  <option value="USD">Dollar (USD)</option>
                  <option value="EUR">Euro (EUR)</option>
                </select>
              </div>
              <div>
                <label htmlFor="date_format" className="block text-sm font-medium text-gray-700">
                  Format Tanggal
                </label>
                <select
                  id="date_format"
                  name="date_format"
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  value={settings.date_format}
                  onChange={handleSettingsChange}
                >
                  <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                  <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                  <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                </select>
              </div>
              <div>
                <label htmlFor="theme" className="block text-sm font-medium text-gray-700">
                  Tema
                </label>
                <select
                  id="theme"
                  name="theme"
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  value={settings.theme}
                  onChange={handleSettingsChange}
                >
                  <option value="light">Terang</option>
                  <option value="dark">Gelap</option>
                  <option value="system">Sistem</option>
                </select>
              </div>
              <div>
                <button
                  type="submit"
                  className="inline-flex justify-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                >
                  Simpan Pengaturan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div className="overflow-hidden rounded-lg bg-white shadow">
        <div className="px-4 py-5 sm:p-6">
          <h2 className="text-lg font-medium leading-6 text-gray-900">Ekspor & Impor</h2>
          <div className="mt-4 space-y-4">
            <div>
              <h3 className="text-sm font-medium text-gray-700">Ekspor Data</h3>
              <p className="mt-1 text-sm text-gray-500">
                Ekspor data transaksi dan kategori Anda ke dalam file untuk cadangan atau analisis.
              </p>
              <div className="mt-2 flex space-x-3">
                <button
                  type="button"
                  onClick={() => exportData('csv')}
                  className="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                >
                  Ekspor ke CSV
                </button>
                <button
                  type="button"
                  onClick={() => exportData('xlsx')}
                  className="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                >
                  Ekspor ke Excel
                </button>
              </div>
            </div>

            <div>
              <h3 className="text-sm font-medium text-gray-700">Impor Data</h3>
              <p className="mt-1 text-sm text-gray-500">
                Impor data transaksi dari file CSV atau Excel.
              </p>
              <form onSubmit={importData} className="mt-2">
                <div className="flex items-center space-x-3">
                  <input
                    type="file"
                    name="import_file"
                    id="import_file"
                    className="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100"
                    accept=".csv,.xlsx,.xls"
                  />
                  <button
                    type="submit"
                    className="inline-flex items-center rounded-md border border-transparent bg-primary-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                  >
                    Impor
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
