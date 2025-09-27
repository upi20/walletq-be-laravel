# ❓ Confirmation Dialog System

Sistem dialog konfirmasi yang telah diimplementasikan untuk aplikasi Laravel Inertia Vue TypeScript.

## 📁 Struktur File

```
resources/js/
├── types/
│   └── confirmation.ts                 # Type definitions untuk confirmation
├── composables/
│   └── useConfirmation.ts             # Composable utama untuk confirmation
├── components/
│   └── Confirmation/
│       ├── ConfirmationDialog.vue     # Component dialog konfirmasi
│       └── ConfirmationContainer.vue  # Container untuk dialog
├── plugins/
│   └── confirmation.ts               # Plugin untuk registrasi global
└── layouts/
    └── FinancialAppLayout.vue        # Layout dengan ConfirmationContainer
```

## 🚀 Penggunaan Dasar

### Import dan Setup

```vue
<script setup lang="ts">
import { useConfirmation } from '@/composables/useConfirmation';

const { confirmDelete, confirmAction, alert } = useConfirmation();
</script>
```

## 🎯 Jenis Confirmation

### 1. Confirm Delete
```javascript
// Simple delete confirmation
const confirmed = await confirmDelete();

// Delete with item name
const confirmed = await confirmDelete('Data Penting');

if (confirmed) {
  // Lakukan proses delete
  deleteItem();
}
```

### 2. Confirm Action
```javascript
const confirmed = await confirmAction(
  'Konfirmasi Tindakan Penting',
  'Apakah Anda yakin ingin melanjutkan proses ini?'
);

if (confirmed) {
  // Lakukan tindakan
  performAction();
}
```

### 3. Alert Dialog
```javascript
await alert(
  'Informasi Penting',
  'Sistem akan mengalami pemeliharaan pada hari Minggu.'
);
// Dialog ditutup setelah user klik OK
```

### 4. Custom Confirmation
```javascript
const confirmed = await showConfirmation({
  title: 'Judul Custom',
  message: 'Pesan detail konfirmasi',
  type: 'warning', // 'danger', 'warning', 'info', 'success'
  confirmText: 'Ya, Lanjutkan',
  cancelText: 'Batal',
  showIcon: true
});
```

## 🎨 Jenis Type dan Styling

| Type | Icon | Warna | Penggunaan |
|------|------|-------|------------|
| `danger` | XCircle | Merah | Delete, tindakan destructive |
| `warning` | AlertTriangle | Kuning | Peringatan, konfirmasi penting |
| `info` | Info | Biru | Informasi, konfirmasi biasa |
| `success` | CheckCircle | Hijau | Konfirmasi positif |

## ⚙️ Options yang Tersedia

| Option | Type | Default | Deskripsi |
|--------|------|---------|-----------|
| `title` | `string` | **Required** | Judul dialog |
| `message` | `string` | `undefined` | Pesan detail |
| `type` | `ConfirmationType` | `'info'` | Jenis dialog |
| `confirmText` | `string` | `'Konfirmasi'` | Text tombol konfirmasi |
| `cancelText` | `string` | `'Batal'` | Text tombol batal |
| `showIcon` | `boolean` | `true` | Tampilkan icon |
| `persistent` | `boolean` | `false` | Tidak bisa ditutup dengan ESC/click outside |
| `confirmButtonClass` | `string` | Auto | Custom CSS class untuk tombol konfirmasi |
| `cancelButtonClass` | `string` | Auto | Custom CSS class untuk tombol batal |

## 🔧 Integration dengan Inertia.js

```javascript
// Delete confirmation dengan Inertia
const handleDelete = async (item) => {
  const confirmed = await confirmDelete(item.name);
  
  if (confirmed) {
    router.delete(`/api/items/${item.id}`, {
      onSuccess: () => {
        toast.success('Item berhasil dihapus!');
      },
      onError: () => {
        toast.error('Gagal menghapus item.');
      }
    });
  }
};

// Form submission dengan konfirmasi
const handleSubmit = async () => {
  if (form.amount > 1000000) {
    const confirmed = await confirmAction(
      'Konfirmasi Jumlah Besar',
      `Anda akan memproses transaksi sebesar ${formatCurrency(form.amount)}. Lanjutkan?`
    );
    
    if (!confirmed) return;
  }
  
  form.post('/api/transactions');
};
```

## 🎯 Advanced Usage

### Custom Dialog dengan Options Lengkap

```javascript
const confirmed = await showConfirmation({
  title: 'Hapus Semua Data',
  message: 'Tindakan ini akan menghapus SEMUA data yang terkait dan tidak dapat dibatalkan. Ketik "HAPUS" untuk mengkonfirmasi.',
  type: 'danger',
  confirmText: 'Ya, Hapus Semua',
  cancelText: 'Batalkan',
  confirmButtonClass: 'bg-red-600 hover:bg-red-700 text-white font-bold',
  persistent: true,
  showIcon: true
});
```

### Global Access (via Plugin)

```javascript
// Dalam component apapun
this.$confirm.confirmDelete('Data Penting');

// Atau via inject
const confirm = inject('confirmation');
confirm.alert('Pesan penting');
```

## 🎮 Keyboard Navigation

- **Enter**: Konfirmasi (tombol confirm)
- **Escape**: Batal (jika tidak persistent)
- **Tab**: Navigasi antar tombol

## 📱 Responsive Design

Dialog secara otomatis responsive dan menyesuaikan dengan ukuran layar:
- Desktop: Center modal dengan backdrop blur
- Mobile: Full width dengan padding optimal

## 🎭 Animations

- **Enter**: Fade in + scale up dari 95% ke 100%
- **Leave**: Fade out + scale down ke 95%
- **Backdrop**: Smooth blur effect

## 🧪 Contoh Penggunaan Real

### 1. Di halaman Accounts/Index.vue
```javascript
// Replace browser confirm dengan custom dialog
const deleteAccount = async (account) => {
  const confirmed = await confirmDelete(account.name);
  
  if (confirmed) {
    // Proses delete dengan toast feedback
    router.delete(`/settings/accounts/${account.id}`, {
      onSuccess: () => toast.success('Akun berhasil dihapus!'),
      onError: () => toast.error('Gagal menghapus akun')
    });
  }
};
```

### 2. Di halaman Create Account
```javascript
// Konfirmasi untuk saldo besar
const submit = async () => {
  if (form.initial_balance > 10000000) {
    const confirmed = await confirmAction(
      'Konfirmasi Saldo Besar',
      `Saldo awal ${formatCurrency(form.initial_balance)} cukup besar. Yakin data sudah benar?`
    );
    
    if (!confirmed) return;
  }
  
  form.post('/settings/accounts');
};
```

## 🔍 Troubleshooting

1. **Dialog tidak muncul**: Pastikan `ConfirmationContainer` sudah di layout
2. **Multiple dialogs**: System otomatis close dialog sebelumnya
3. **Keyboard tidak berfungsi**: Pastikan dialog mendapat focus
4. **Styling tidak sesuai**: Check Tailwind CSS classes

## 🎉 Fitur Lengkap

- ✅ 4 jenis konfirmasi (danger, warning, info, success)
- ✅ Custom button text dan styling
- ✅ Keyboard navigation (Enter, Escape)
- ✅ Auto-focus untuk accessibility
- ✅ Backdrop blur dengan smooth animation
- ✅ Persistent mode untuk dialog penting
- ✅ Icon yang sesuai dengan jenis dialog
- ✅ Dark/light mode support
- ✅ TypeScript support penuh
- ✅ Global plugin access
- ✅ Mobile responsive
- ✅ Integration dengan Inertia.js & Toast

## 📋 Demo Testing

Lihat implementasi demo di halaman `/settings/accounts`:
- Tombol **"❓ Confirm"** untuk test berbagai jenis dialog
- Tombol **"Delete"** pada setiap akun untuk real-world usage
- Form create account dengan konfirmasi saldo besar