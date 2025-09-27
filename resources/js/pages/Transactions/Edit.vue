<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { 
  ArrowLeft,
  Save,
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
import { Account, TransactionCategory, Tag, Transaction } from '@/types';

interface Props {
  transaction: Transaction;
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
const showAccountDropdown = ref(false);
const showCategoryDropdown = ref(false);
const accountSearch = ref('');
const categorySearch = ref('');

// Format angka dengan titik sebagai pemisah ribuan
const formatAmount = (value: string): string => {
  if (!value) return '';
  // Hapus semua titik dan karakter non-numeric
  const cleanValue = value.replace(/[^\d]/g, '');
  if (!cleanValue) return '';
  // Format dengan titik pemisah ribuan
  return cleanValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

// Display values for amount fields
const displayAmount = ref('');

// Split date and time from transaction.date
const transactionDate = new Date(props.transaction.date);

const year = transactionDate.getUTCFullYear();
const month = String(transactionDate.getUTCMonth() + 1).padStart(2, "0");
const day = String(transactionDate.getUTCDate()).padStart(2, "0");

const hours = String(transactionDate.getUTCHours()).padStart(2, "0");
const minutes = String(transactionDate.getUTCMinutes()).padStart(2, "0");

const dateString = `${year}-${month}-${day}`; // 2025-09-27
const timeString = `${hours}:${minutes}`;     // 18:15

// Form with pre-filled data
const form = useForm({
  type: props.transaction.type as 'income' | 'expense',
  account_id: props.transaction.account_id?.toString() || '',
  transaction_category_id: props.transaction.transaction_category_id?.toString() || '',
  amount: props.transaction.amount.toString(),
  date: dateString,
  time: timeString,
  note: props.transaction.note || '',
  flag: props.transaction.flag || 'normal',
  tag_ids: props.transaction.tags?.map(tag => tag.id) || [] as number[],
});

// Initialize display amount
displayAmount.value = formatAmount(props.transaction.amount.toString());

// Parse angka kembali ke format database
const parseAmount = (value: string): number => {
  if (!value) return 0;
  const cleanValue = value.replace(/\./g, '');
  return parseFloat(cleanValue) || 0;
};

// Handle amount input
const handleAmountInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const inputValue = target.value;
  
  // Format untuk display
  const formatted = formatAmount(inputValue);
  displayAmount.value = formatted;
  target.value = formatted;
  
  // Set nilai asli ke form
  form.amount = parseAmount(formatted).toString();
};

// Computed
const availableCategories = computed(() => {
  const categories = form.type === 'income' 
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
  const categories = form.type === 'income' 
    ? props.masterData.income_categories 
    : props.masterData.expense_categories;
  
  if (!Array.isArray(categories)) return [];
  
  if (!categorySearch.value) return categories;
  return categories.filter(category => 
    category.name.toLowerCase().includes(categorySearch.value.toLowerCase())
  );
});

const getSelectedAccount = computed(() => {
  const accounts = props.masterData.accounts;
  if (!Array.isArray(accounts)) return null;
  
  return accounts.find(a => a.id.toString() === form.account_id);
});

const getSelectedCategory = computed(() => {
  const categories = availableCategories.value;
  if (!Array.isArray(categories)) return null;
  
  return categories.find(c => c.id.toString() === form.transaction_category_id);
});

// Error computed properties
const hasAccountError = computed(() => {
  return form.errors && form.errors['account_id'];
});

const hasCategoryError = computed(() => {
  return form.errors && form.errors['transaction_category_id'];
});

const hasAmountError = computed(() => {
  return form.errors && form.errors['amount'];
});

const hasDateError = computed(() => {
  return form.errors && form.errors['date'];
});

// Select methods
const selectAccount = (accountId: number) => {
  form.account_id = accountId.toString();
  showAccountDropdown.value = false;
  accountSearch.value = '';
};

const selectCategory = (categoryId: number) => {
  form.transaction_category_id = categoryId.toString();
  showCategoryDropdown.value = false;
  categorySearch.value = '';
};

// Tag methods
const toggleTag = (tagId: number) => {
  const index = form.tag_ids.indexOf(tagId);
  if (index === -1) {
    form.tag_ids.push(tagId);
  } else {
    form.tag_ids.splice(index, 1);
  }
};

// Submit form
const submitForm = () => {
  // Client-side validation
  const errors = validateForm();
  if (errors.length > 0) {
    error(`Mohon lengkapi: ${errors.join(', ')}`);
    return;
  }

  const data = form.data();
  const formData = {
    ...data,
    amount: parseAmount(data.amount),
    date: `${data.date} ${data.time}:00` // Combine date and time
  };
  
  form.transform(() => formData).put(`/transactions/${props.transaction.id}`, {
    onSuccess: () => {
      success('Transaksi berhasil diperbarui');
    },
    onError: (errors) => {
      handleFormErrors(errors);
    }
  });
};

// Validation
const validateForm = (): string[] => {
  const errors: string[] = [];
  
  if (!form.account_id) {
    errors.push('Akun');
  }
  
  if (!form.transaction_category_id) {
    errors.push('Kategori');
  }
  
  if (!form.amount || parseAmount(form.amount) <= 0) {
    errors.push('Jumlah yang valid');
  }
  
  if (!form.date) {
    errors.push('Tanggal');
  }
  
  if (!form.time) {
    errors.push('Waktu');
  }
  
  return errors;
};

// Handle form errors
const handleFormErrors = (errors: any) => {
  const errorMessages: string[] = [];
  
  Object.keys(errors).forEach(key => {
    if (Array.isArray(errors[key])) {
      errorMessages.push(...errors[key]);
    } else {
      errorMessages.push(errors[key]);
    }
  });
  
  if (errorMessages.length > 0) {
    error(`Terjadi kesalahan: ${errorMessages.join(', ')}`);
  }
};

// Dropdown close on click outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement;
  if (!target.closest('.dropdown-container')) {
    showAccountDropdown.value = false;
    showCategoryDropdown.value = false;
    accountSearch.value = '';
    categorySearch.value = '';
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
      <div class="flex items-center justify-between mb-4">
        <Link 
          href="/transactions" 
          class="p-2 bg-white/20 hover:bg-white/30 rounded-xl text-white transition-colors duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center"
        >
          <ArrowLeft class="w-5 h-5" />
        </Link>
        <h1 class="text-xl font-semibold text-white truncate flex-1 text-center">{{ pageTitle }}</h1>
        <div class="w-[44px]"></div> <!-- Spacer -->
      </div>
    </div>

    <!-- Main Form Content -->
    <div class="px-4">
      <form @submit.prevent="submitForm()" class="bg-white dark:bg-gray-800 rounded-xl shadow-md border-gray-200 dark:border-gray-700 p-6 mb-4">
        <!-- Type Toggle (Income/Expense) -->
        <div class="mb-6">
          <div class="flex bg-gray-100 dark:bg-gray-700 rounded-xl p-1">
            <button 
              type="button" 
              @click="form.type = 'income'" 
              :class="form.type === 'income' ? 'bg-teal-500 text-white shadow-md' : 'text-gray-700 dark:text-gray-300'" 
              class="flex-1 py-2 rounded-lg text-sm font-medium transition-all duration-200"
            >
              Pemasukan
            </button>
            <button 
              type="button" 
              @click="form.type = 'expense'" 
              :class="form.type === 'expense' ? 'bg-coral-500 text-white shadow-md' : 'text-gray-700 dark:text-gray-300'" 
              class="flex-1 py-2 rounded-lg text-sm font-medium transition-all duration-200"
            >
              Pengeluaran
            </button>
          </div>
        </div>

        <!-- Account Selection with Search -->
        <div class="mb-4 relative dropdown-container">
          <button 
            @click="showAccountDropdown = !showAccountDropdown" 
            :class="[
              'w-full px-4 py-3 border rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent flex items-center justify-between transition-colors duration-200',
              hasAccountError 
                ? 'border-red-500 dark:border-red-400' 
                : 'border-gray-200 dark:border-gray-700'
            ]"
            type="button"
          >
            <div class="flex items-center gap-2">
              <CreditCard class="w-4 h-4 text-gray-500" />
              <span class="text-gray-900 dark:text-white">{{ getSelectedAccount?.name || 'Pilih Akun' }}</span>
            </div>
            <ChevronDown class="w-4 h-4 text-gray-500" />
          </button>
          
          <div v-if="showAccountDropdown" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-50">
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
            <div class="max-h-64 overflow-y-auto overscroll-contain">
              <button 
                v-for="account in filteredAccounts" 
                :key="account.id" 
                @click="selectAccount(account.id)" 
                class="w-full p-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between border-none bg-transparent focus:bg-gray-50 dark:focus:bg-gray-700 transition-colors"
                type="button"
              >
                <span class="text-gray-900 dark:text-white truncate">{{ account.name }}</span>
                <span class="text-sm text-gray-500 ml-2 flex-shrink-0">{{ formatCurrency(account.current_balance, 'decimal') }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Category Selection with Search -->
        <div class="mb-4 relative dropdown-container">
          <button 
            @click="showCategoryDropdown = !showCategoryDropdown" 
            :class="[
              'w-full px-4 py-3 border rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent flex items-center justify-between transition-colors duration-200',
              hasCategoryError 
                ? 'border-red-500 dark:border-red-400' 
                : 'border-gray-200 dark:border-gray-700'
            ]"
            type="button"
          >
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 rounded-full" :class="form.type === 'income' ? 'bg-teal-500' : 'bg-coral-500'"></div>
              <span class="text-gray-900 dark:text-white">{{ getSelectedCategory?.name || 'Pilih Kategori' }}</span>
            </div>
            <ChevronDown class="w-4 h-4 text-gray-500" />
          </button>
          
          <div v-if="showCategoryDropdown" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-50">
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
            <div class="max-h-64 overflow-y-auto overscroll-contain">
              <button 
                v-for="category in filteredCategories" 
                :key="category.id" 
                @click="selectCategory(category.id)" 
                class="w-full p-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 border-none bg-transparent focus:bg-gray-50 dark:focus:bg-gray-700 transition-colors"
                type="button"
              >
                <div class="w-3 h-3 rounded-full flex-shrink-0" :class="category.type === 'income' ? 'bg-teal-500' : 'bg-coral-500'"></div>
                <span class="text-gray-900 dark:text-white truncate">{{ category.name }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Amount with Floating Label -->
        <div class="mb-4 relative">
          <input
            @input="handleAmountInput"
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
              v-model="form.date"
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
              v-model="form.time"
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
            v-model="form.note"
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
              type="button"
              v-for="tag in masterData.tags"
              :key="tag.id"
              @click="toggleTag(tag.id)"
              :class="form.tag_ids.includes(tag.id) ? 'bg-teal-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
              class="px-3 py-1 rounded-full text-sm font-medium transition-colors duration-200"
            >
              {{ tag.name }}
            </button>
          </div>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white font-semibold py-4 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 shadow-lg disabled:opacity-50"
        >
          <Save class="w-5 h-5" />
          {{ form.processing ? 'Menyimpan...' : 'Perbarui Transaksi' }}
        </button>
      </form>
    </div>
  </FinancialAppLayout>
</template>
