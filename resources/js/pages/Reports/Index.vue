<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import {
  Search,
  Filter,
  ChevronLeft,
  ChevronRight,
  ChevronDown,
  Calendar,
  TrendingUp,
  TrendingDown,
  DollarSign,
  ChevronRight as ChevronRightIcon,
  X
} from 'lucide-vue-next';

import FinancialAppLayout from '@/layouts/FinancialAppLayout.vue';
import formatCurrency from '@/composables/formatCurrency';

// Components - REUSE from Transactions
import SearchModal from '../Transactions/Partials/SearchModal.vue';
import FilterModal from '../Transactions/Partials/FilterModal.vue';
import PieChart from '@/components/PieChart.vue';

interface CategoryData {
  category_id: number;
  category_name: string;
  total_amount: number;
  transaction_count: number;
  percentage: number;
}

interface Props {
  summary: {
    total_income: number;
    total_expense: number;
    net_amount: number;
    transaction_count: number;
  };
  incomeByCategory: CategoryData[];
  expenseByCategory: CategoryData[];
  masterData: {
    accounts: any[];
    income_categories: any[];
    expense_categories: any[];
    tags: any[];
    flag_options: any[];
    type_options: any[];
    period_options: any[];
  };
  filters: {
    period?: string;
    month?: string;
    year?: string;
    date_from?: string;
    date_to?: string;
    type?: string;
    search?: string;
    account_ids?: number[];
    category_ids?: number[];
    tag_ids?: number[];
    flags?: string[];
    amount_min?: number;
    amount_max?: number;
  };
}

const props = defineProps<Props>();

// State
const loading = ref(false);
const showSearchModal = ref(false);
const showFilterModal = ref(false);
const showMonthPicker = ref(false);

// Current filters state
const currentFilters = ref({
  ...props.filters
});

// Watch for props changes and update current filters
watch(() => props.filters, (newFilters) => {
  currentFilters.value = { ...newFilters };
}, { immediate: true, deep: true });

// Dark mode detection (read-only, reactive)
const isDark = ref(false);

// Update dark mode state on mount and when it changes
if (typeof document !== 'undefined') {
  isDark.value = document.documentElement.classList.contains('dark');

  // Watch for dark mode changes
  const observer = new MutationObserver(() => {
    isDark.value = document.documentElement.classList.contains('dark');
  });

  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
  });
}

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

// Generate month options
const monthOptions = computed(() => {
  const options = [];
  const now = new Date();

  for (let i = -12; i <= 6; i++) {
    const date = new Date(now.getFullYear(), now.getMonth() + i, 1);
    const value = date.toISOString().slice(0, 7);
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

const applyFilters = (filters: any) => {
  loading.value = true;

  if (filters.period !== 'month') {
    filters.month = null;
  }

  // Clean up empty values
  const cleanFilters = Object.fromEntries(
    Object.entries(filters).filter(([_, value]) => {
      if (Array.isArray(value)) return value.length > 0;
      return value !== null && value !== undefined && value !== '';
    })
  );

  router.get('/reports', cleanFilters, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      loading.value = false;
    },
    onError: () => {
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

const toggleMonthPicker = () => {
  showMonthPicker.value = !showMonthPicker.value;
};

const selectMonth = (month: string) => {
  applyFilters({ ...currentFilters.value, month, period: 'month' });
  showMonthPicker.value = false;
};

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
</script>

<template>
  <FinancialAppLayout :showHeader="false" :showFab="false" containerClass="px-0 pb-32">

    <Head title="Laporan Keuangan" />

    <!-- Header Section with Title and Action Buttons -->
    <div
      class="bg-gradient-to-r from-teal-500 to-teal-600 dark:from-teal-600 dark:to-teal-700 rounded-b-[24px] px-4 pt-4 pb-6 mb-4 shadow-lg dark:shadow-2xl">
      <!-- Header: Title + Search + Filter Buttons -->
      <div class="flex items-center justify-between mb-4">
        <div class="flex-1 min-w-0">
          <h1 class="text-xl font-semibold text-white truncate">Laporan Keuangan</h1>
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

      <!-- Month Navigation (only show for month period) -->
      <div v-if="(currentFilters.period || 'month') === 'month'"
        class="flex items-center justify-between mb-4 bg-white/10 rounded-xl p-3">
        <button
          @click="previousMonth"
          class="p-2 hover:bg-white/20 rounded-lg text-white transition-colors duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center">
          <ChevronLeft class="w-5 h-5" />
        </button>

        <div class="relative">
          <button
            @click="toggleMonthPicker"
            class="flex items-center gap-2 text-white cursor-pointer hover:bg-white/10 rounded-lg px-2 py-1 transition-colors duration-200">
            <Calendar class="w-4 h-4" />
            <span class="font-medium">{{ currentPeriodLabel }}</span>
            <ChevronDown class="w-3 h-3 transition-transform duration-200"
              :class="{ 'rotate-180': showMonthPicker }" />
          </button>

          <!-- Month Picker Dropdown -->
          <div
            v-if="showMonthPicker"
            class="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 z-50 max-h-64 overflow-y-auto">
            <button
              v-for="option in monthOptions"
              :key="option.value"
              @click="selectMonth(option.value)"
              class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 text-gray-900 dark:text-white"
              :class="{ 'bg-teal-50 dark:bg-teal-900/20 text-teal-600 dark:text-teal-400': option.value === (currentFilters.month || new Date().toISOString().slice(0, 7)) }">
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

      <!-- 3 Summary Cards -->
      <div class="grid grid-cols-3 gap-3">
        <!-- Income -->
        <div class="bg-white/10 rounded-xl p-3 text-center">
          <TrendingUp class="w-4 h-4 text-white mx-auto mb-1" />
          <p class="text-xs text-white/70 mb-1">Masuk</p>
          <p class="text-sm font-bold text-white">
            {{ formatCurrency(summary.total_income, 'decimal') }}
          </p>
        </div>

        <!-- Expense -->
        <div class="bg-white/10 rounded-xl p-3 text-center">
          <TrendingDown class="w-4 h-4 text-white mx-auto mb-1" />
          <p class="text-xs text-white/70 mb-1">Keluar</p>
          <p class="text-sm font-bold text-white">
            {{ formatCurrency(summary.total_expense, 'decimal') }}
          </p>
        </div>

        <!-- Net -->
        <div class="bg-white/10 rounded-xl p-3 text-center">
          <DollarSign class="w-4 h-4 text-white mx-auto mb-1" />
          <p class="text-xs text-white/70 mb-1">Selisih</p>
          <p class="text-sm font-bold" :class="summary.net_amount >= 0 ? 'text-white' : 'text-coral-200'">
            {{ formatCurrency(summary.net_amount, 'decimal') }}
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
          class="text-xs text-teal-600 hover:text-teal-700 font-medium">
          Hapus Semua
        </button>
      </div>

      <div class="flex flex-wrap gap-2">
        <!-- Search Filter -->
        <div v-if="currentFilters.search"
          class="flex items-center gap-1 px-3 py-1 bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 rounded-full text-sm">
          <Search class="w-3 h-3" />
          <span>{{ currentFilters.search }}</span>
          <button @click="removeFilter('search')"
            class="ml-1 hover:bg-teal-200 dark:hover:bg-teal-800 rounded-full p-0.5">
            <X class="w-3 h-3" />
          </button>
        </div>

        <!-- Type Filter -->
        <div v-if="currentFilters.type && currentFilters.type !== 'both'"
          class="flex items-center gap-1 px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm">
          <span>{{ currentFilters.type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}</span>
          <button @click="removeFilter('type')"
            class="ml-1 hover:bg-blue-200 dark:hover:bg-blue-800 rounded-full p-0.5">
            <X class="w-3 h-3" />
          </button>
        </div>

        <!-- Account Filters -->
        <div v-for="accountId in (currentFilters.account_ids || [])" :key="`account-${accountId}`"
          class="flex items-center gap-1 px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-sm">
          <span>{{masterData.accounts.find(a => a.id === accountId)?.name || `Account ${accountId}`}}</span>
          <button @click="removeAccountFilter(accountId)"
            class="ml-1 hover:bg-green-200 dark:hover:bg-green-800 rounded-full p-0.5">
            <X class="w-3 h-3" />
          </button>
        </div>

        <!-- Category Filters -->
        <div v-for="categoryId in (currentFilters.category_ids || [])" :key="`category-${categoryId}`"
          class="flex items-center gap-1 px-3 py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-full text-sm">
          <span>{{[...masterData.income_categories, ...masterData.expense_categories].find(c => c.id ===
            categoryId)?.name || `Category ${categoryId}`}}</span>
          <button @click="removeCategoryFilter(categoryId)"
            class="ml-1 hover:bg-purple-200 dark:hover:bg-purple-800 rounded-full p-0.5">
            <X class="w-3 h-3" />
          </button>
        </div>

        <!-- Amount Range Filter -->
        <div v-if="currentFilters.amount_min || currentFilters.amount_max"
          class="flex items-center gap-1 px-3 py-1 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 rounded-full text-sm">
          <DollarSign class="w-3 h-3" />
          <span>
            {{ currentFilters.amount_min ? formatCurrency(currentFilters.amount_min, 'decimal') : '0' }} -
            {{ currentFilters.amount_max ? formatCurrency(currentFilters.amount_max, 'decimal') : '∞' }}
          </span>
          <button @click="removeFilter('amount')"
            class="ml-1 hover:bg-orange-200 dark:hover:bg-orange-800 rounded-full p-0.5">
            <X class="w-3 h-3" />
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content: Income & Expense Sections -->
    <div class="px-4 space-y-6">
      <!-- Income Section -->
      <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-md dark:shadow-2xl border-0 dark:border dark:border-gray-700 overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <TrendingUp class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                Pendapatan
              </h2>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ incomeByCategory.length }} kategori • {{ formatCurrency(summary.total_income, 'currency') }}
              </p>
            </div>
            <Link
              href="/reports/income"
              class="px-4 py-2 bg-gradient-to-r from-teal-500 to-teal-600 dark:from-teal-600 dark:to-teal-700 text-white rounded-xl hover:shadow-lg transition-all duration-300 text-sm font-medium min-h-[44px] flex items-center">
              Lihat Detail
            </Link>
          </div>
        </div>

        <!-- Income Pie Chart -->
        <div v-if="incomeByCategory.length > 0" class="p-5 border-b border-gray-100 dark:border-gray-700">
          <PieChart
            :data="incomeByCategory"
            type="income"
            :is-dark="isDark" />
        </div>

        <!-- Income Categories List -->
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
          <div
            v-for="category in incomeByCategory.slice(0, 5)"
            :key="category.category_id"
            class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 min-h-[64px]">
            <!-- Icon -->
            <div
              class="w-10 h-10 rounded-full bg-teal-100 dark:bg-teal-900 flex items-center justify-center dark:border dark:border-white/10">
              <TrendingUp class="w-5 h-5 text-teal-600 dark:text-teal-400" />
            </div>

            <!-- Content -->
            <div class="flex-1 ml-3">
              <h4 class="font-medium text-gray-900 dark:text-white">{{ category.category_name }}</h4>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ category.transaction_count }} transaksi • {{ category.percentage }}%
              </p>
            </div>

            <!-- Amount -->
            <div class="text-right mr-2">
              <p class="font-semibold text-gray-900 dark:text-white">
                {{ formatCurrency(category.total_amount, 'decimal') }}
              </p>
            </div>

            <!-- Arrow -->
            <ChevronRightIcon class="w-5 h-5 text-gray-400" />
          </div>

          <!-- Empty State -->
          <div v-if="incomeByCategory.length === 0" class="p-8 text-center">
            <TrendingUp class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" />
            <p class="text-gray-500 dark:text-gray-400">Tidak ada data pendapatan</p>
          </div>
        </div>
      </div>

      <!-- Expense Section -->
      <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-md dark:shadow-2xl border-0 dark:border dark:border-gray-700 overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <TrendingDown class="w-5 h-5 text-coral-600 dark:text-coral-400" />
                Pengeluaran
              </h2>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ expenseByCategory.length }} kategori • {{ formatCurrency(summary.total_expense, 'currency') }}
              </p>
            </div>
            <Link
              href="/reports/expense"
              class="px-4 py-2 bg-gradient-to-r from-coral-500 to-coral-600 dark:from-coral-600 dark:to-coral-700 text-white rounded-xl hover:shadow-lg transition-all duration-300 text-sm font-medium min-h-[44px] flex items-center">
              Lihat Detail
            </Link>
          </div>
        </div>

        <!-- Expense Pie Chart -->
        <div v-if="expenseByCategory.length > 0" class="p-5 border-b border-gray-100 dark:border-gray-700">
          <PieChart
            :data="expenseByCategory"
            type="expense"
            :is-dark="isDark" />
        </div>

        <!-- Expense Categories List -->
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
          <div
            v-for="category in expenseByCategory.slice(0, 5)"
            :key="category.category_id"
            class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 min-h-[64px]">
            <!-- Icon -->
            <div
              class="w-10 h-10 rounded-full bg-coral-100 dark:bg-coral-900 flex items-center justify-center dark:border dark:border-white/10">
              <TrendingDown class="w-5 h-5 text-coral-600 dark:text-coral-400" />
            </div>

            <!-- Content -->
            <div class="flex-1 ml-3">
              <h4 class="font-medium text-gray-900 dark:text-white">{{ category.category_name }}</h4>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ category.transaction_count }} transaksi • {{ category.percentage }}%
              </p>
            </div>

            <!-- Amount -->
            <div class="text-right mr-2">
              <p class="font-semibold text-gray-900 dark:text-white">
                {{ formatCurrency(category.total_amount, 'decimal') }}
              </p>
            </div>

            <!-- Arrow -->
            <ChevronRightIcon class="w-5 h-5 text-gray-400" />
          </div>

          <!-- Empty State -->
          <div v-if="expenseByCategory.length === 0" class="p-8 text-center">
            <TrendingDown class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" />
            <p class="text-gray-500 dark:text-gray-400">Tidak ada data pengeluaran</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Search Modal -->
    <SearchModal
      v-if="showSearchModal"
      :initial-query="currentFilters.search || ''"
      @apply="applySearchFilter"
      @close="closeSearchModal" />

    <!-- Filter Modal -->
    <FilterModal
      v-if="showFilterModal"
      :filters="{ ...currentFilters, month: currentFilters.month ?? undefined }"
      :master-data="{
        accounts: masterData.accounts?.map(account => ({
          id: account.id,
          name: account.name,
          current_balance: account.current_balance,
          category: account.category ? {
            id: account.category.id,
            name: account.category.name,
            type: account.category.type || ''
          } : null
        })) || [],
        income_categories: masterData.income_categories,
        expense_categories: masterData.expense_categories,
        tags: masterData.tags,
        flag_options: masterData.flag_options,
        type_options: masterData.type_options,
        period_options: masterData.period_options
      }"
      @apply="applyFilters"
      @close="closeFilterModal" />
  </FinancialAppLayout>
</template>
