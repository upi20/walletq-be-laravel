# 🔔 Toast Notification System

Sistem notifikasi toast yang telah diimplementasikan untuk aplikasi Laravel Inertia Vue TypeScript.

## 📁 Struktur File

```
resources/js/
├── types/
│   └── toast.ts                    # Type definitions untuk toast
├── composables/
│   └── useToast.ts                 # Composable utama untuk toast
├── components/
│   └── Toast/
│       ├── ToastItem.vue           # Component untuk item toast
│       └── ToastContainer.vue      # Container untuk menampilkan toast
├── plugins/
│   └── toast.ts                    # Plugin untuk registrasi global
└── layouts/
    └── FinancialAppLayout.vue      # Layout dengan ToastContainer
```

## 🚀 Penggunaan Dasar

### Import dan Setup

```vue
<script setup lang="ts">
import { useToast } from '@/composables/useToast';

const { success, error, warning, info } = useToast();
</script>
```

### Contoh Penggunaan

#### 1. Success Toast
```javascript
success('Operasi berhasil!');

// With options
success('Data berhasil disimpan!', {
  message: 'Perubahan telah tersimpan ke database.',
  duration: 5000
});
```

#### 2. Error Toast
```javascript
error('Terjadi kesalahan!');

// Persistent error (tidak hilang otomatis)
error('Error kritis', {
  message: 'Hubungi administrator sistem.',
  persistent: true
});
```

#### 3. Warning Toast
```javascript
warning('Peringatan!', {
  message: 'Pastikan data yang dimasukkan sudah benar.',
  duration: 6000
});
```

#### 4. Info Toast
```javascript
info('Informasi', {
  message: 'Fitur ini akan segera tersedia.'
});
```

## ⚙️ Options yang Tersedia

| Option | Type | Default | Deskripsi |
|--------|------|---------|-----------|
| `message` | `string` | `undefined` | Pesan detail tambahan |
| `duration` | `number` | `4000` (success/warning/info), `6000` (error) | Durasi tampil dalam ms |
| `persistent` | `boolean` | `false` | Toast tidak hilang otomatis |

## 🎨 Styling dan Theme

Toast secara otomatis menyesuaikan dengan dark/light mode:

- **Success**: Hijau dengan ikon CheckCircle
- **Error**: Merah dengan ikon XCircle  
- **Warning**: Kuning dengan ikon AlertTriangle
- **Info**: Biru dengan ikon Info

## 🔧 Integration dengan Inertia.js

```javascript
// Dalam form submission
form.post('/api/endpoint', {
  onStart: () => {
    info('Memproses...', {
      message: 'Sedang menyimpan data.',
      duration: 2000
    });
  },
  onSuccess: () => {
    success('Berhasil!', {
      message: 'Data berhasil disimpan.',
      duration: 4000
    });
  },
  onError: (errors) => {
    error('Gagal menyimpan', {
      message: 'Terjadi kesalahan. Silakan coba lagi.',
      persistent: true
    });
  }
});
```

## 🎯 Advanced Usage

### Manual Toast Management

```javascript
import { useToast } from '@/composables/useToast';

const { addToast, removeToast, clearAllToasts } = useToast();

// Add custom toast
const toastId = addToast('success', 'Custom Toast', {
  message: 'This is a custom toast',
  duration: 3000
});

// Remove specific toast
removeToast(toastId);

// Clear all toasts
clearAllToasts();
```

### Global Access (via Plugin)

```javascript
// Dalam component apapun
this.$toast.success('Global access!');

// Atau via inject
const toast = inject('toast');
toast.success('Via inject!');
```

## 📱 Responsive Design

Toast container diposisikan di:
- Desktop: Top-right corner
- Mobile: Menyesuaikan dengan lebar layar

## 🧪 Testing Demo

Lihat implementasi demo di halaman `/settings/accounts` dengan tombol "🔔 Demo" yang menampilkan berbagai jenis toast secara berurutan.

## 🔍 Troubleshooting

1. **Toast tidak muncul**: Pastikan `ToastContainer` sudah diimport di layout
2. **Styling tidak sesuai**: Periksa Tailwind CSS classes
3. **TypeScript errors**: Pastikan semua types sudah diimport dengan benar

## 🎉 Fitur Lengkap

- ✅ 4 jenis toast (success, error, warning, info)
- ✅ Auto-dismiss dengan custom duration
- ✅ Persistent toast option
- ✅ Dark/light mode support
- ✅ Smooth animations
- ✅ Click to dismiss
- ✅ Queue management
- ✅ TypeScript support
- ✅ Global plugin access
- ✅ Integration dengan Inertia.js
- ✅ Responsive design