<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import {
  Search,
  Filter,
  ChevronLeft,
  ChevronRight,
  ChevronDown,
  Calendar,
  DollarSign,
  TrendingUp,
  TrendingDown,
  X,
  Plus
} from 'lucide-vue-next';

import FinancialAppLayout from '@/layouts/FinancialAppLayout.vue';
import { useTranslation } from '@/composables/useTranslation';
import { useToast } from '@/composables/useToast';
import formatCurrency from '@/composables/formatCurrency';

// Components
import SearchModal from './Partials/SearchModal.vue';
import FilterModal from './Partials/FilterModal.vue';
import TransactionCard from './Partials/TransactionCard.vue';
import { TransactionListResponse, TransactionFilters } from '@/types';

interface Props {
  transactions: TransactionListResponse;
  pageTitle: string;
}


const props = defineProps<Props>();

// Translation
const { trans } = useTranslation();

// Toast notifications
const { success, error, warning, info } = useToast();

// State
const loading = ref(false);
const showSearchModal = ref(false);
const showFilterModal = ref(false);
const showMonthPicker = ref(false);

// Current filters state
const currentFilters = ref<TransactionFilters>({
  ...props.transactions.filters
});

// Watch for props changes and update current filters
watch(() => props.transactions.filters, (newFilters) => {
  currentFilters.value = { ...newFilters };
}, { immediate: true, deep: true });

// Computed
const currentPeriodLabel = computed(() => {
  const period = currentFilters.value.period || 'month';
  
  if (period === 'today') return 'Hari Ini';
  if (period === 'week') return 'Minggu Ini';
  if (period === 'year') return 'Tahun Ini';
  if (period === 'all') return 'Semua Waktu';
  
  if (period === 'month') {
    const month = currentFilters.value.month || new Date().toISOString().slice(0, 7);
    const date = new Date(month + '-01');
    return date.toLocaleDateString('id-ID', {
      month: 'long',
      year: 'numeric'
    });
  }
  
  if (period === 'custom' && currentFilters.value.date_from && currentFilters.value.date_to) {
    return `${currentFilters.value.date_from} - ${currentFilters.value.date_to}`;
  }
  
  return 'Filter Aktif';
});

const hasActiveFilters = computed(() => {
  const filters = currentFilters.value;
  return !!(
    filters.search ||
    (filters.account_ids && filters.account_ids.length > 0) ||
    (filters.category_ids && filters.category_ids.length > 0) ||
    (filters.tag_ids && filters.tag_ids.length > 0) ||
    (filters.flags && filters.flags.length > 0) ||
    filters.type !== 'both' ||
    filters.amount_min ||
    filters.amount_max ||
    filters.period !== 'month'
  );
});

// Generate month options (last 12 months + next 6 months)
const monthOptions = computed(() => {
  const options = [];
  const now = new Date();
  
  // Generate from 12 months ago to 6 months ahead
  for (let i = -12; i <= 6; i++) {
    const date = new Date(now.getFullYear(), now.getMonth() + i, 1);
    const value = date.toISOString().slice(0, 7); // YYYY-MM format
    const label = date.toLocaleDateString('id-ID', {
      month: 'long',
      year: 'numeric'
    });
    
    options.push({ value, label });
  }
  
  return options;
});


// Methods
const openSearchModal = () => {
  showSearchModal.value = true;
};

const closeSearchModal = () => {
  showSearchModal.value = false;
};

const openFilterModal = () => {
  showFilterModal.value = true;
};

const closeFilterModal = () => {
  showFilterModal.value = false;
};

const previousMonth = () => {
  const currentMonth = currentFilters.value.month || new Date().toISOString().slice(0, 7);
  const date = new Date(currentMonth + '-01');
  date.setMonth(date.getMonth() - 1);
  const newMonth = date.toISOString().slice(0, 7);
  
  applyFilters({ ...currentFilters.value, month: newMonth, period: 'month' });
};

const nextMonth = () => {
  const currentMonth = currentFilters.value.month || new Date().toISOString().slice(0, 7);
  const date = new Date(currentMonth + '-01');
  date.setMonth(date.getMonth() + 1);
  const newMonth = date.toISOString().slice(0, 7);
  
  applyFilters({ ...currentFilters.value, month: newMonth, period: 'month' });
};

const applySearchFilter = (query: string) => {
  applyFilters({ ...currentFilters.value, search: query });
  closeSearchModal();
};

const applyFilters = (filters: TransactionFilters) => {
  loading.value = true;
  
  // Clean up empty values
  const cleanFilters = Object.fromEntries(
    Object.entries(filters).filter(([_, value]) => {
      if (Array.isArray(value)) return value.length > 0;
      return value !== null && value !== undefined && value !== '';
    })
  );

  router.get('/transactions', cleanFilters, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      loading.value = false;
    },
    onError: () => {
      error('Gagal memuat data transaksi');
      loading.value = false;
    }
  });
};

const clearAllFilters = () => {
  applyFilters({
    period: 'month',
    month: new Date().toISOString().slice(0, 7),
    type: 'both'
  });
};
const quickFilterByPeriod = (period: 'month' | 'year' | 'today' | 'week' | 'all' | 'custom' | undefined) => {
  applyFilters({ ...currentFilters.value, period });
};

const dateFormatter = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};

// Filter removal methods
const removeFilter = (filterType: string) => {
  const newFilters = { ...currentFilters.value };
  
  if (filterType === 'search') {
    delete newFilters.search;
  } else if (filterType === 'type') {
    newFilters.type = 'both';
  } else if (filterType === 'amount') {
    delete newFilters.amount_min;
    delete newFilters.amount_max;
  }
  
  applyFilters(newFilters);
};

const removeAccountFilter = (accountId: number) => {
  const newFilters = { ...currentFilters.value };
  newFilters.account_ids = (newFilters.account_ids || []).filter(id => id !== accountId);
  applyFilters(newFilters);
};

const removeCategoryFilter = (categoryId: number) => {
  const newFilters = { ...currentFilters.value };
  newFilters.category_ids = (newFilters.category_ids || []).filter(id => id !== categoryId);
  applyFilters(newFilters);
};

// Click actions for TransactionCard
const onAccountClick = (accountId: number) => {
  const currentAccountIds = currentFilters.value.account_ids || [];
  const newAccountIds = currentAccountIds.includes(accountId) 
    ? currentAccountIds.filter(id => id !== accountId)
    : [...currentAccountIds, accountId];
  
  applyFilters({ ...currentFilters.value, account_ids: newAccountIds });
};

const onCategoryClick = (categoryId: number) => {
  const currentCategoryIds = currentFilters.value.category_ids || [];
  const newCategoryIds = currentCategoryIds.includes(categoryId)
    ? currentCategoryIds.filter(id => id !== categoryId)
    : [...currentCategoryIds, categoryId];
  
  applyFilters({ ...currentFilters.value, category_ids: newCategoryIds });
};

const handleTransactionAction = (action: string, transactionId: number) => {
  switch (action) {
    case 'view':
      info('Fitur lihat detail akan segera hadir');
      break;
    case 'edit':
      info('Fitur edit transaksi akan segera hadir');
      break;
    case 'delete':
      warning('Fitur hapus transaksi akan segera hadir');
      break;
  }
};

const toggleMonthPicker = () => {
  showMonthPicker.value = !showMonthPicker.value;
};

const selectMonth = (month: string) => {
  applyFilters({ ...currentFilters.value, month, period: 'month' });
  showMonthPicker.value = false;
};

// Watch for URL changes
watch(() => currentFilters.value, (newFilters) => {
  // Update URL parameters when filters change
}, { deep: true });

onMounted(() => {
  // Initialize any needed data
});
</script>

<template>
  <FinancialAppLayout :showHeader="false" :showFab="false" containerClass="px-0 pb-32">

    <Head :title="pageTitle" />

    <!-- Header Section with Title and Action Buttons -->
    <div
      class="bg-gradient-to-r from-teal-500 to-teal-600 dark:from-teal-600 dark:to-teal-700 rounded-b-[24px] px-4 pt-4 pb-6 mb-4 shadow-lg dark:shadow-2xl">
      <!-- Header: Title + Search + Filter Buttons -->
      <div class="flex items-center justify-between mb-4">
        <div class="flex-1 min-w-0">
          <h1 class="text-xl font-semibold text-white truncate">{{ pageTitle }}</h1>
          <p class="text-sm text-teal-100 dark:text-teal-200 truncate">{{ currentPeriodLabel }}</p>
        </div>

        <div class="flex items-center gap-2 ml-3">
          <button
            @click="openSearchModal"
            class="p-3 bg-white/20 hover:bg-white/30 rounded-xl text-white transition-colors duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center"
            title="Cari">
            <Search class="w-5 h-5" />
          </button>

          <button
            @click="openFilterModal"
            :class="hasActiveFilters ? 'bg-white/30 ring-2 ring-white/50' : 'bg-white/20 hover:bg-white/30'"
            class="p-3 rounded-xl text-white transition-colors duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center relative"
            title="Filter">
            <Filter class="w-5 h-5" />
            <div v-if="hasActiveFilters" class="absolute -top-1 -right-1 w-3 h-3 bg-white rounded-full"></div>
          </button>
        </div>
      </div>

      <!-- Quick Period Filters -->
      <div class="mb-4">
        <div class="flex overflow-x-auto pb-2 -mx-4 px-4 gap-2 scrollbar-hide">
          <button
            v-for="period in transactions.master_data.period_options"
            :key="period.value"
            @click="quickFilterByPeriod(period.value as 'month' | 'year' | 'today' | 'week' | 'all' | 'custom' | undefined)"
            class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 whitespace-nowrap min-h-[36px]"
            :class="currentFilters.period === period.value || (!currentFilters.period && period.value === 'month')
              ? 'bg-white text-teal-600 shadow-md'
              : 'bg-white/20 text-white hover:bg-white/30'"
          >
            {{ period.label }}
          </button>
        </div>
      </div>

      <!-- Month Navigation (only show for month period) -->
      <div v-if="(currentFilters.period || 'month') === 'month'" class="flex items-center justify-between mb-4 bg-white/10 rounded-xl p-3">
        <button
          @click="previousMonth"
          class="p-2 hover:bg-white/20 rounded-lg text-white transition-colors duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center">
          <ChevronLeft class="w-5 h-5" />
        </button>

        <div class="relative">
          <button 
            @click="toggleMonthPicker"
            class="flex items-center gap-2 text-white cursor-pointer hover:bg-white/10 rounded-lg px-2 py-1 transition-colors duration-200"
          >
            <Calendar class="w-4 h-4" />
            <span class="font-medium">{{ currentPeriodLabel }}</span>
            <ChevronDown class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': showMonthPicker }" />
          </button>

          <!-- Month Picker Dropdown -->
          <div 
            v-if="showMonthPicker"
            class="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 z-50 max-h-64 overflow-y-auto"
          >
            <button
              v-for="option in monthOptions"
              :key="option.value"
              @click="selectMonth(option.value)"
              class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 text-gray-900 dark:text-white"
              :class="{ 'bg-teal-50 dark:bg-teal-900/20 text-teal-600 dark:text-teal-400': option.value === (currentFilters.month || new Date().toISOString().slice(0, 7)) }"
            >
              {{ option.label }}
            </button>
          </div>
        </div>

        <button
          @click="nextMonth"
          class="p-2 hover:bg-white/20 rounded-lg text-white transition-colors duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center">
          <ChevronRight class="w-5 h-5" />
        </button>
      </div>

      <!-- 3 Summary Cards for Current Month -->
      <!-- 3 Cards Summary -->
      <div class="grid grid-cols-3 gap-3">
        <!-- Income -->
        <div class="bg-white/10 rounded-xl p-3 text-center">
          <TrendingUp class="w-4 h-4 text-white mx-auto mb-1" />
          <p class="text-xs text-white/70 mb-1">Masuk</p>
          <p class="text-sm font-bold text-white">
            {{ formatCurrency(transactions.summary.total_income, 'decimal') }}
          </p>
        </div>
        
        <!-- Expense -->
        <div class="bg-white/10 rounded-xl p-3 text-center">
          <TrendingDown class="w-4 h-4 text-white mx-auto mb-1" />
          <p class="text-xs text-white/70 mb-1">Keluar</p>
          <p class="text-sm font-bold text-white">
            {{ formatCurrency(transactions.summary.total_expense, 'decimal') }}
          </p>
        </div>
        
        <!-- Net -->
        <div class="bg-white/10 rounded-xl p-3 text-center">
          <DollarSign class="w-4 h-4 text-white mx-auto mb-1" />
          <p class="text-xs text-white/70 mb-1">Selisih</p>
          <p class="text-sm font-bold" :class="transactions.summary.net_amount >= 0 ? 'text-white' : 'text-coral-200'">
            {{ formatCurrency(transactions.summary.net_amount, 'decimal') }}
          </p>
        </div>
      </div>
    </div>

    <!-- Active Filters Chips -->
    <div v-if="hasActiveFilters" class="px-4 mb-4">
      <div class="flex items-center gap-2 mb-2">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter Aktif:</span>
        <button
          @click="clearAllFilters"
          class="text-xs text-teal-600 hover:text-teal-700 font-medium"
        >
          Hapus Semua
        </button>
      </div>
      
      <div class="flex flex-wrap gap-2">
        <!-- Search Filter -->
        <div v-if="currentFilters.search" class="flex items-center gap-1 px-3 py-1 bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 rounded-full text-sm">
          <Search class="w-3 h-3" />
          <span>{{ currentFilters.search }}</span>
          <button @click="removeFilter('search')" class="ml-1 hover:bg-teal-200 dark:hover:bg-teal-800 rounded-full p-0.5">
            <X class="w-3 h-3" />
          </button>
        </div>
        
        <!-- Type Filter -->
        <div v-if="currentFilters.type && currentFilters.type !== 'both'" class="flex items-center gap-1 px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm">
          <span>{{ currentFilters.type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}</span>
          <button @click="removeFilter('type')" class="ml-1 hover:bg-blue-200 dark:hover:bg-blue-800 rounded-full p-0.5">
            <X class="w-3 h-3" />
          </button>
        </div>
        
        <!-- Account Filters -->
        <div v-for="accountId in (currentFilters.account_ids || [])" :key="`account-${accountId}`" class="flex items-center gap-1 px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-sm">
          <span>{{ transactions.master_data.accounts.find(a => a.id === accountId)?.name || `Account ${accountId}` }}</span>
          <button @click="removeAccountFilter(accountId)" class="ml-1 hover:bg-green-200 dark:hover:bg-green-800 rounded-full p-0.5">
            <X class="w-3 h-3" />
          </button>
        </div>
        
        <!-- Category Filters -->
        <div v-for="categoryId in (currentFilters.category_ids || [])" :key="`category-${categoryId}`" class="flex items-center gap-1 px-3 py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-full text-sm">
          <span>{{ [...transactions.master_data.income_categories, ...transactions.master_data.expense_categories].find(c => c.id === categoryId)?.name || `Category ${categoryId}` }}</span>
          <button @click="removeCategoryFilter(categoryId)" class="ml-1 hover:bg-purple-200 dark:hover:bg-purple-800 rounded-full p-0.5">
            <X class="w-3 h-3" />
          </button>
        </div>
        
        <!-- Amount Range Filter -->
        <div v-if="currentFilters.amount_min || currentFilters.amount_max" class="flex items-center gap-1 px-3 py-1 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 rounded-full text-sm">
          <DollarSign class="w-3 h-3" />
          <span>
            {{ currentFilters.amount_min ? formatCurrency(currentFilters.amount_min, 'decimal') : '0' }} - 
            {{ currentFilters.amount_max ? formatCurrency(currentFilters.amount_max, 'decimal') : '∞' }}
          </span>
          <button @click="removeFilter('amount')" class="ml-1 hover:bg-orange-200 dark:hover:bg-orange-800 rounded-full p-0.5">
            <X class="w-3 h-3" />
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content: Daily Grouped Transactions -->
    <div class="px-2">
      <div v-for="group in transactions.data" :key="group.date"
        class="bg-white dark:bg-gray-800 shadow-md border-gray-200 dark:border-gray-700 rounded-xl mb-4 overflow-visible">
        <!-- Date Header with Subtotal -->
        <div class="bg-gray-50/95 dark:bg-gray-700/95 px-4 py-3 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-20 backdrop-blur-sm rounded-t-xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Calendar class="w-4 h-4 text-gray-500 dark:text-gray-400" />
              <span class="text-sm font-medium text-gray-900 dark:text-white">
                {{ dateFormatter(group.date) }}
              </span>
            </div>

            <div class="text-right">
              <span
                class="text-sm font-semibold"
                :class="group.amount >= 0
                  ? 'text-teal-600 dark:text-teal-400'
                  : 'text-coral-600 dark:text-coral-400'">
                {{ formatCurrency(group.amount, 'currency') }}
              </span>
            </div>
          </div>
        </div>
        <!-- Transactions in this date -->
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
          <TransactionCard
            v-for="transaction in group.transactions"
            :key="transaction.id"
            :transaction="transaction"
            @action="handleTransactionAction"
            @account-click="onAccountClick"
            @category-click="onCategoryClick" />
        </div>
      </div>

      <div v-if="transactions.data.length === 0" class="text-center py-12 px-4">
        <div class="text-gray-400 mb-6">
          <DollarSign class="w-16 h-16 mx-auto mb-4 opacity-50" />
          <p class="text-lg font-medium">Tidak ada transaksi</p>
          <p class="text-sm text-gray-500 mt-1">
            {{ hasActiveFilters
              ? 'Tidak ada transaksi yang sesuai dengan filter yang dipilih'
              : `Belum ada transaksi di ${$page.props.currentMonthLabel}`
            }}
          </p>
        </div>

        <div class="flex flex-col gap-3">
          <button
            v-if="hasActiveFilters"
            @click="clearAllFilters"
            class="w-full py-3 text-teal-600 border border-teal-600 rounded-xl hover:bg-teal-50 dark:hover:bg-teal-900/20 transition-colors duration-200 font-medium">
            Hapus Filter
          </button>

          <Link
            href="/transactions/create"
            class="w-full py-3 bg-teal-500 text-white rounded-xl hover:bg-teal-600 transition-colors duration-200 font-medium text-center">
          Tambah Transaksi
          </Link>
        </div>
      </div>
    </div>

    <!-- Search Modal -->
    <SearchModal 
      v-if="showSearchModal" 
      :initial-query="currentFilters.search || ''"
      @apply="applySearchFilter"
      @close="closeSearchModal" 
    />
    
    <!-- Filter Modal -->
    <FilterModal 
      v-if="showFilterModal"
      :filters="currentFilters"
      :master-data="{
        accounts: transactions.master_data.accounts?.map(account => ({
          id: account.id,
          name: account.name,
          current_balance: account.current_balance,
          category: account.category ? {
            id: account.category.id,
            name: account.category.name,
            type: account.category.type || ''
          } : null
        })) || [],
        income_categories: transactions.master_data.income_categories,
        expense_categories: transactions.master_data.expense_categories,
        tags: transactions.master_data.tags,
        flag_options: transactions.master_data.flag_options,
        type_options: transactions.master_data.type_options,
        period_options: transactions.master_data.period_options
      }"
      @apply="applyFilters"
      @close="closeFilterModal" 
    />

    <Link
      href="/transactions/create"
      title="Buat transaksi"
      class="fixed bottom-20 right-6 w-14 h-14 bg-gradient-to-r from-teal-500 to-teal-600 dark:from-teal-600 dark:to-teal-700 rounded-full shadow-xl flex items-center justify-center transition-all duration-300 hover:scale-105 active:scale-95"
    >
      <Plus class="w-6 h-6 text-white" />
    </Link>

  </FinancialAppLayout>
</template>

<style scoped>
/* Hide scrollbar for period filters */
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -webkit-overflow-scrolling: touch;
}
</style>
