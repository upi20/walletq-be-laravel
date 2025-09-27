<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { 
  ArrowLeft,
  Save,
  Plus,
  Trash2,
  Copy,
  Calendar,
  CreditCard,
  Tag as TagIcon,
  DollarSign,
  FileText,
  Search,
  ChevronDown,
  Clock
} from 'lucide-vue-next';
import FinancialAppLayout from '@/layouts/FinancialAppLayout.vue';
import { useTranslation } from '@/composables/useTranslation';
import { useToast } from '@/composables/useToast';
import formatCurrency from '@/composables/formatCurrency';
import { Account, TransactionCategory, Tag } from '@/types';

interface Props {
  masterData: {
    accounts: Account[];
    income_categories: TransactionCategory[];
    expense_categories: TransactionCategory[];
    tags: Tag[];
    flag_options: Array<{ value: string; label: string; }>;
  };
  pageTitle: string;
}

const props = defineProps<Props>();

// Translation & Toast
const { trans } = useTranslation();
const { success, error, warning, info } = useToast();

// State
const bulkMode = ref(false);
const showAccountDropdown = ref(false);
const showCategoryDropdown = ref(false);
const accountSearch = ref('');
const categorySearch = ref('');

// Display values for amount fields
const displayAmount = ref('');
const bulkDisplayAmounts = ref<string[]>(['']); // Initialize dengan satu empty string

// Single transaction form
const singleForm = useForm({
  type: 'expense' as 'income' | 'expense',
  account_id: '',
  transaction_category_id: '',
  amount: '',
  date: new Date().toISOString().slice(0, 10),
  time: new Date().toTimeString().slice(0, 5),
  note: '',
  flag: 'normal',
  tag_ids: [] as number[],
});

// Bulk transactions data
const bulkTransactions = ref([
  {
    type: 'expense' as 'income' | 'expense',
    account_id: '',
    transaction_category_id: '',
    amount: '',
    date: new Date().toISOString().slice(0, 10),
    time: new Date().toTimeString().slice(0, 5),
    note: '',
    flag: 'normal',
    tag_ids: [] as number[],
  }
]);

// Computed
const availableCategories = computed(() => {
  if (bulkMode.value) return [];
  const categories = singleForm.type === 'income' 
    ? props.masterData.income_categories 
    : props.masterData.expense_categories;
  
  // Ensure categories is an array
  return Array.isArray(categories) ? categories : [];
});

const filteredAccounts = computed(() => {
  if (!accountSearch.value) return props.masterData.accounts;
  return props.masterData.accounts.filter(account => 
    account.name.toLowerCase().includes(accountSearch.value.toLowerCase())
  );
});

const filteredCategories = computed(() => {
  const categories = singleForm.type === 'income' 
    ? props.masterData.income_categories 
    : props.masterData.expense_categories;
  
  // Ensure categories is an array
  if (!Array.isArray(categories)) return [];
  
  if (!categorySearch.value) return categories;
  return categories.filter(category => 
    category.name.toLowerCase().includes(categorySearch.value.toLowerCase())
  );
});

const getBulkCategories = (type: 'income' | 'expense') => {
  const categories = type === 'income' 
    ? props.masterData.income_categories 
    : props.masterData.expense_categories;
  
  // Ensure categories is an array
  return Array.isArray(categories) ? categories : [];
};

// Error states for visual feedback
const hasAccountError = computed(() => {
  return singleForm.errors && singleForm.errors['transactions.0.account_id'];
});

const hasCategoryError = computed(() => {
  return singleForm.errors && singleForm.errors['transactions.0.transaction_category_id'];
});

const hasAmountError = computed(() => {
  return singleForm.errors && singleForm.errors['transactions.0.amount'];
});

const hasDateError = computed(() => {
  return singleForm.errors && singleForm.errors['transactions.0.date'];
});

// Methods
const toggleBulkMode = () => {
  bulkMode.value = !bulkMode.value;
  if (!bulkMode.value && bulkTransactions.value.length > 1) {
    bulkTransactions.value = [bulkTransactions.value[0]];
  }
};

const addBulkTransaction = () => {
  bulkTransactions.value.push({
    type: 'expense',
    account_id: '',
    transaction_category_id: '',
    amount: '',
    date: new Date().toISOString().slice(0, 10),
    time: new Date().toTimeString().slice(0, 5),
    note: '',
    flag: 'normal',
    tag_ids: [],
  });
  
  // Initialize display amount
  bulkDisplayAmounts.value.push('');
};

// Format angka dengan titik sebagai pemisah ribuan
const formatAmount = (value: string): string => {
  if (!value) return '';
  // Hapus semua titik dan karakter non-numeric
  const cleanValue = value.replace(/[^\d]/g, '');
  if (!cleanValue) return '';
  // Format dengan titik pemisah ribuan
  return cleanValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

// Parse angka kembali ke format database
const parseAmount = (value: string): number => {
  if (!value) return 0;
  const cleanValue = value.replace(/\./g, '');
  return parseFloat(cleanValue) || 0;
};

// Handle single form amount input
const handleSingleAmountInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const inputValue = target.value;
  
  // Format untuk display
  const formatted = formatAmount(inputValue);
  displayAmount.value = formatted;
  target.value = formatted;
  
  // Set nilai asli ke form
  singleForm.amount = parseAmount(formatted).toString();
};

// Handle bulk form amount input
const handleBulkAmountInput = (event: Event, index: number) => {
  const target = event.target as HTMLInputElement;
  const inputValue = target.value;
  
  // Format untuk display
  const formatted = formatAmount(inputValue);
  bulkDisplayAmounts.value[index] = formatted;
  target.value = formatted;
  
  // Set nilai asli ke form
  bulkTransactions.value[index].amount = parseAmount(formatted).toString();
};

// Select dropdown methods
const selectAccount = (accountId: number) => {
  singleForm.account_id = accountId.toString();
  showAccountDropdown.value = false;
  accountSearch.value = '';
};

const selectCategory = (categoryId: number) => {
  singleForm.transaction_category_id = categoryId.toString();
  showCategoryDropdown.value = false;
  categorySearch.value = '';
};

const getSelectedAccount = computed(() => {
  return props.masterData.accounts.find(a => a.id.toString() === singleForm.account_id);
});

const getSelectedCategory = computed(() => {
  const categories = singleForm.type === 'income' 
    ? props.masterData.income_categories 
    : props.masterData.expense_categories;
  
  // Ensure categories is an array
  if (!Array.isArray(categories)) return null;
  
  return categories.find(c => c.id.toString() === singleForm.transaction_category_id);
});

const removeBulkTransaction = (index: number) => {
  if (bulkTransactions.value.length > 1) {
    bulkTransactions.value.splice(index, 1);
    bulkDisplayAmounts.value.splice(index, 1);
  }
};

const duplicateBulkTransaction = (index: number) => {
  const transaction = { ...bulkTransactions.value[index] };
  transaction.tag_ids = [...transaction.tag_ids];
  bulkTransactions.value.splice(index + 1, 0, transaction);
  
  // Duplicate display amount
  const displayAmount = bulkDisplayAmounts.value[index] || '';
  bulkDisplayAmounts.value.splice(index + 1, 0, displayAmount);
};

const submitSingle = () => {
  // Client-side validation
  const errors = validateSingleForm();
  if (errors.length > 0) {
    error(`Mohon lengkapi: ${errors.join(', ')}`);
    return;
  }

  const data = singleForm.data();
  const formData = {
    transactions: [{
      ...data,
      amount: parseAmount(data.amount),
      date: `${data.date} ${data.time}:00` // Combine date and time
    }]
  };
  
  singleForm.transform(() => formData).post('/transactions', {
    onSuccess: () => {
      success('Transaksi berhasil ditambahkan');
    },
    onError: (errors) => {
      handleFormErrors(errors);
    }
  });
};

const submitBulk = () => {
  // Validate bulk transactions
  const validationResults = validateBulkTransactions();
  
  if (validationResults.errors.length > 0) {
    error(`Transaksi ${validationResults.invalidIndexes.map(i => i + 1).join(', ')}: ${validationResults.errors[0]}`);
    return;
  }

  if (validationResults.validTransactions.length === 0) {
    warning('Lengkapi minimal satu transaksi yang valid');
    return;
  }

  const formData = {
    transactions: validationResults.validTransactions.map(t => ({
      ...t,
      amount: parseAmount(t.amount),
      date: `${t.date} ${t.time}:00` // Combine date and time
    }))
  };

  singleForm.transform(() => formData).post('/transactions', {
    onSuccess: () => {
      success(`Berhasil menambahkan ${validationResults.validTransactions.length} transaksi`);
    },
    onError: (errors) => {
      handleFormErrors(errors);
    }
  });
};

const toggleTag = (tagId: number, transactionIndex?: number) => {
  if (bulkMode.value && transactionIndex !== undefined) {
    const tagIds = bulkTransactions.value[transactionIndex].tag_ids;
    const index = tagIds.indexOf(tagId);
    if (index > -1) {
      tagIds.splice(index, 1);
    } else {
      tagIds.push(tagId);
    }
  } else {
    const index = singleForm.tag_ids.indexOf(tagId);
    if (index > -1) {
      singleForm.tag_ids.splice(index, 1);
    } else {
      singleForm.tag_ids.push(tagId);
    }
  }
};

// Validation functions
const validateSingleForm = (): string[] => {
  const errors: string[] = [];
  
  if (!singleForm.account_id) {
    errors.push('Akun');
  }
  
  if (!singleForm.transaction_category_id) {
    errors.push('Kategori');
  }
  
  if (!singleForm.amount || parseAmount(singleForm.amount) <= 0) {
    errors.push('Jumlah yang valid');
  }
  
  if (!singleForm.date) {
    errors.push('Tanggal');
  }
  
  if (!singleForm.time) {
    errors.push('Waktu');
  }
  
  return errors;
};

const validateBulkTransactions = () => {
  const validTransactions: typeof bulkTransactions.value = [];
  const invalidIndexes: number[] = [];
  const errors: string[] = [];
  
  bulkTransactions.value.forEach((transaction, index) => {
    const transactionErrors: string[] = [];
    
    if (!transaction.account_id) transactionErrors.push('akun');
    if (!transaction.transaction_category_id) transactionErrors.push('kategori');
    if (!transaction.amount || parseAmount(transaction.amount) <= 0) transactionErrors.push('jumlah');
    if (!transaction.date) transactionErrors.push('tanggal');
    if (!transaction.time) transactionErrors.push('waktu');
    
    if (transactionErrors.length > 0) {
      invalidIndexes.push(index);
      if (errors.length === 0) { // Only show first error type
        errors.push(`mohon lengkapi ${transactionErrors.join(', ')}`);
      }
    } else {
      validTransactions.push(transaction);
    }
  });
  
  return { validTransactions, invalidIndexes, errors };
};

const handleFormErrors = (errors: any) => {
  console.log('Form errors:', errors);
  
  if (errors?.transactions) {
    // Handle transaction-specific errors
    const transactionErrors = errors.transactions;
    const errorMessages: string[] = [];
    
    Object.keys(transactionErrors).forEach(key => {
      const fieldErrors = transactionErrors[key];
      Object.keys(fieldErrors).forEach(field => {
        const messages = fieldErrors[field];
        if (Array.isArray(messages)) {
          errorMessages.push(...messages);
        }
      });
    });
    
    if (errorMessages.length > 0) {
      error(errorMessages[0]); // Show first error
    } else {
      error('Terjadi kesalahan pada form');
    }
  } else if (errors?.message) {
    error(errors.message);
  } else {
    error('Gagal menyimpan transaksi. Periksa kembali data yang dimasukkan.');
  }
};

// Close dropdowns when clicking outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as Element;
  if (!target.closest('.dropdown-container')) {
    showAccountDropdown.value = false;
    showCategoryDropdown.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
  <FinancialAppLayout :showHeader="false" :showFab="false" containerClass="px-0 pb-32">
    <Head :title="pageTitle" />

    <!-- Header Section -->
    <div class="bg-gradient-to-r from-teal-500 to-teal-600 dark:from-teal-600 dark:to-teal-700 rounded-b-[24px] px-4 pt-4 pb-6 mb-4 shadow-lg dark:shadow-2xl">
      <!-- Header: Back Button + Title + Bulk Toggle -->
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <Link
            href="/transactions"
            class="p-2 bg-white/20 hover:bg-white/30 rounded-xl text-white transition-colors duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center"
          >
            <ArrowLeft class="w-5 h-5" />
          </Link>
          <div>
            <h1 class="text-xl font-semibold text-white">{{ pageTitle }}</h1>
            <p class="text-sm text-teal-100 dark:text-teal-200">{{ bulkMode ? 'Mode Bulk' : 'Transaksi Tunggal' }}</p>
          </div>
        </div>

        <button
          @click="toggleBulkMode"
          :class="bulkMode ? 'bg-white/30 ring-2 ring-white/50' : 'bg-white/20 hover:bg-white/30'"
          class="p-3 rounded-xl text-white transition-colors duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center"
          title="Toggle Bulk Mode"
        >
          <Copy class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Single Transaction Form -->
    <div v-if="!bulkMode" class="px-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border-gray-200 dark:border-gray-700 p-6 mb-4">
        
        <!-- Transaction Type Toggle -->
        <div class="mb-6">
          <div class="flex bg-gray-100 dark:bg-gray-700 rounded-xl p-1">
            <button
              @click="singleForm.type = 'income'"
              :class="singleForm.type === 'income' 
                ? 'bg-teal-500 text-white shadow-md' 
                : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
              class="flex-1 py-2 rounded-lg text-sm font-medium transition-all duration-200"
            >
              Pemasukan
            </button>
            <button
              @click="singleForm.type = 'expense'"
              :class="singleForm.type === 'expense' 
                ? 'bg-coral-500 text-white shadow-md' 
                : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
              class="flex-1 py-2 rounded-lg text-sm font-medium transition-all duration-200"
            >
              Pengeluaran
            </button>
          </div>
        </div>

        <!-- Account Selection with Search -->
        <div class="mb-4 relative dropdown-container">
          <div class="relative">
            <button
              @click="showAccountDropdown = !showAccountDropdown"
              :class="[
                'w-full px-4 py-3 border rounded-xl bg-white dark:bg-gray-800 text-left flex items-center justify-between transition-colors duration-200',
                hasAccountError 
                  ? 'border-red-500 dark:border-red-400 focus:ring-2 focus:ring-red-500' 
                  : 'border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-teal-500 focus:border-transparent'
              ]"
            >
              <div class="flex items-center gap-2">
                <CreditCard class="w-4 h-4 text-gray-500" />
                <span class="text-gray-900 dark:text-white">
                  {{ getSelectedAccount?.name || 'Pilih Akun' }}
                </span>
              </div>
              <ChevronDown class="w-4 h-4 text-gray-500" />
            </button>

            <!-- Account Dropdown -->
            <div v-if="showAccountDropdown" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-50">
              <!-- Search -->
              <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                <div class="relative">
                  <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input
                    v-model="accountSearch"
                    type="text"
                    placeholder="Cari akun..."
                    class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700 focus:ring-2 focus:ring-teal-500"
                  />
                </div>
              </div>
              <!-- Options -->
              <div class="max-h-64 overflow-y-auto overscroll-contain">
                <button
                  v-for="account in filteredAccounts"
                  :key="account.id"
                  @click="selectAccount(account.id)"
                  class="w-full p-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between border-none bg-transparent focus:bg-gray-50 dark:focus:bg-gray-700 transition-colors"
                >
                  <span class="text-gray-900 dark:text-white truncate">{{ account.name }}</span>
                  <span class="text-sm text-gray-500 ml-2 flex-shrink-0">{{ formatCurrency(account.current_balance, 'decimal') }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Category Selection with Search -->
        <div class="mb-4 relative dropdown-container">
          <div class="relative">
            <button
              @click="showCategoryDropdown = !showCategoryDropdown"
              :class="[
                'w-full px-4 py-3 border rounded-xl bg-white dark:bg-gray-800 text-left flex items-center justify-between transition-colors duration-200',
                hasCategoryError 
                  ? 'border-red-500 dark:border-red-400 focus:ring-2 focus:ring-red-500' 
                  : 'border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-teal-500 focus:border-transparent'
              ]"
            >
              <div class="flex items-center gap-2">
                <div 
                  class="w-3 h-3 rounded-full"
                  :class="singleForm.type === 'income' ? 'bg-teal-500' : 'bg-coral-500'"
                ></div>
                <span class="text-gray-900 dark:text-white">
                  {{ getSelectedCategory?.name || 'Pilih Kategori' }}
                </span>
              </div>
              <ChevronDown class="w-4 h-4 text-gray-500" />
            </button>

            <!-- Category Dropdown -->
            <div v-if="showCategoryDropdown" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-50">
              <!-- Search -->
              <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                <div class="relative">
                  <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input
                    v-model="categorySearch"
                    type="text"
                    placeholder="Cari kategori..."
                    class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700 focus:ring-2 focus:ring-teal-500"
                  />
                </div>
              </div>
              <!-- Options -->
              <div class="max-h-64 overflow-y-auto overscroll-contain">
                <button
                  v-for="category in filteredCategories"
                  :key="category.id"
                  @click="selectCategory(category.id)"
                  class="w-full p-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 border-none bg-transparent focus:bg-gray-50 dark:focus:bg-gray-700 transition-colors"
                >
                  <div 
                    class="w-3 h-3 rounded-full flex-shrink-0"
                    :class="category.type === 'income' ? 'bg-teal-500' : 'bg-coral-500'"
                  ></div>
                  <span class="text-gray-900 dark:text-white truncate">{{ category.name }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Amount with Floating Label -->
        <div class="mb-4 relative">
          <input
            @input="handleSingleAmountInput"
            type="text"
            placeholder=" "
            :value="displayAmount"
            :class="[
              'peer w-full px-4 py-3 border rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-transparent transition-colors duration-200',
              hasAmountError 
                ? 'border-red-500 dark:border-red-400 focus:ring-2 focus:ring-red-500' 
                : 'border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-teal-500 focus:border-transparent'
            ]"
            required
          />
          <label class="absolute left-4 text-gray-500 transition-all duration-200 peer-placeholder-shown:top-3 peer-placeholder-shown:text-base peer-focus:-top-2 peer-focus:left-3 peer-focus:text-xs peer-focus:text-teal-500 peer-focus:bg-white dark:peer-focus:bg-gray-800 peer-focus:px-1 -top-2 text-xs bg-white dark:bg-gray-800 px-1">
            <DollarSign class="w-3 h-3 inline mr-1" />
            Jumlah
          </label>
        </div>

        <!-- Date and Time with Floating Labels -->
        <div class="grid grid-cols-2 gap-3 mb-4">
          <div class="relative">
            <input
              v-model="singleForm.date"
              type="date"
              placeholder=" "
              :class="[
                'peer w-full px-4 py-3 border rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-transparent transition-colors duration-200',
                hasDateError 
                  ? 'border-red-500 dark:border-red-400 focus:ring-2 focus:ring-red-500' 
                  : 'border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-teal-500 focus:border-transparent'
              ]"
              required
            />
            <label class="absolute left-4 text-gray-500 transition-all duration-200 peer-placeholder-shown:top-3 peer-placeholder-shown:text-base peer-focus:-top-2 peer-focus:left-3 peer-focus:text-xs peer-focus:text-teal-500 peer-focus:bg-white dark:peer-focus:bg-gray-800 peer-focus:px-1 -top-2 text-xs bg-white dark:bg-gray-800 px-1">
              <Calendar class="w-3 h-3 inline mr-1" />
              Tanggal
            </label>
          </div>
          <div class="relative">
            <input
              v-model="singleForm.time"
              type="time"
              placeholder=" "
              class="peer w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent placeholder-transparent"
              required
            />
            <label class="absolute left-4 text-gray-500 transition-all duration-200 peer-placeholder-shown:top-3 peer-placeholder-shown:text-base peer-focus:-top-2 peer-focus:left-3 peer-focus:text-xs peer-focus:text-teal-500 peer-focus:bg-white dark:peer-focus:bg-gray-800 peer-focus:px-1 -top-2 text-xs bg-white dark:bg-gray-800 px-1">
              <Clock class="w-3 h-3 inline mr-1" />
              Waktu
            </label>
          </div>
        </div>

        <!-- Note with Floating Label -->
        <div class="mb-4 relative">
          <textarea
            v-model="singleForm.note"
            placeholder=" "
            rows="2"
            class="peer w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent resize-none placeholder-transparent"
          ></textarea>
          <label class="absolute left-4 text-gray-500 transition-all duration-200 peer-placeholder-shown:top-3 peer-placeholder-shown:text-base peer-focus:-top-2 peer-focus:left-3 peer-focus:text-xs peer-focus:text-teal-500 peer-focus:bg-white dark:peer-focus:bg-gray-800 peer-focus:px-1 -top-2 text-xs bg-white dark:bg-gray-800 px-1">
            <FileText class="w-3 h-3 inline mr-1" />
            Catatan (Opsional)
          </label>
        </div>

        <!-- Tags -->
        <div class="mb-6">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 block">
            <TagIcon class="w-4 h-4 inline mr-2" />
            Tag (Opsional)
          </label>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="tag in masterData.tags"
              :key="tag.id"
              @click="toggleTag(tag.id)"
              :class="singleForm.tag_ids.includes(tag.id)
                ? 'bg-teal-500 text-white'
                : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
              class="px-3 py-1 rounded-full text-sm font-medium transition-colors duration-200"
            >
              {{ tag.name }}
            </button>
          </div>
        </div>

        <!-- Submit Button -->
        <button
          @click="submitSingle"
          :disabled="singleForm.processing"
          class="w-full bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white font-semibold py-4 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 shadow-lg disabled:opacity-50"
        >
          <Save class="w-5 h-5" />
          {{ singleForm.processing ? 'Menyimpan...' : 'Simpan Transaksi' }}
        </button>
      </div>
    </div>

    <!-- Bulk Transaction Form -->
    <div v-else class="px-4">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
          Transaksi Bulk ({{ bulkTransactions.length }})
        </h2>
        <button
          @click="addBulkTransaction"
          class="bg-teal-500 hover:bg-teal-600 text-white p-2 rounded-lg transition-colors duration-200"
        >
          <Plus class="w-5 h-5" />
        </button>
      </div>

      <!-- Bulk Transactions List -->
      <div class="space-y-4 mb-6">
        <div
          v-for="(transaction, index) in bulkTransactions"
          :key="index"
          class="bg-white dark:bg-gray-800 rounded-xl shadow-md border-gray-200 dark:border-gray-700 p-4"
        >
          <!-- Transaction Header with Actions -->
          <div class="flex items-center justify-between mb-4">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
              Transaksi #{{ index + 1 }}
            </span>
            <div class="flex gap-2">
              <button
                @click="duplicateBulkTransaction(index)"
                class="text-gray-500 hover:text-teal-600 p-1"
                title="Duplikat"
              >
                <Copy class="w-4 h-4" />
              </button>
              <button
                v-if="bulkTransactions.length > 1"
                @click="removeBulkTransaction(index)"
                class="text-gray-500 hover:text-red-600 p-1"
                title="Hapus"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Transaction Form Fields (Compact Layout) -->
          <div class="grid grid-cols-2 gap-3 mb-3">
            <!-- Type -->
            <div>
              <select
                v-model="transaction.type"
                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500"
              >
                <option value="income">Pemasukan</option>
                <option value="expense">Pengeluaran</option>
              </select>
            </div>

            <!-- Amount -->
            <div>
              <input
                @input="handleBulkAmountInput($event, index)"
                type="text"
                placeholder="Jumlah"
                :value="bulkDisplayAmounts[index] || ''"
                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3 mb-3">
            <!-- Account -->
            <div>
              <select
                v-model="transaction.account_id"
                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500"
              >
                <option value="">Pilih Akun</option>
                <option v-for="account in masterData.accounts" :key="account.id" :value="account.id">
                  {{ account.name }}
                </option>
              </select>
            </div>

            <!-- Category -->
            <div>
              <select
                v-model="transaction.transaction_category_id"
                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500"
              >
                <option value="">Pilih Kategori</option>
                <option v-for="category in getBulkCategories(transaction.type)" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Date, Time and Note -->
          <div class="grid grid-cols-3 gap-2 mb-3">
            <input
              v-model="transaction.date"
              type="date"
              class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500"
            />
            <input
              v-model="transaction.time"
              type="time"
              class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500"
            />
            <input
              v-model="transaction.note"
              type="text"
              placeholder="Catatan"
              class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500"
            />
          </div>

          <!-- Tags (Compact) -->
          <div class="flex flex-wrap gap-1">
            <button
              v-for="tag in masterData.tags"
              :key="tag.id"
              @click="toggleTag(tag.id, index)"
              :class="transaction.tag_ids.includes(tag.id)
                ? 'bg-teal-500 text-white'
                : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'"
              class="px-2 py-1 rounded text-xs transition-colors duration-200"
            >
              {{ tag.name }}
            </button>
          </div>
        </div>
      </div>

      <!-- Bulk Submit Button -->
      <button
        @click="submitBulk"
        :disabled="singleForm.processing"
        class="w-full bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white font-semibold py-4 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 shadow-lg disabled:opacity-50"
      >
        <Save class="w-5 h-5" />
        {{ singleForm.processing ? 'Menyimpan...' : `Simpan ${bulkTransactions.length} Transaksi` }}
      </button>
    </div>

  </FinancialAppLayout>
</template>
