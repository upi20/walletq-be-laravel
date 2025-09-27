<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { 
  Search, 
  Filter,
  ChevronLeft,
  ChevronRight,
  Calendar,
  DollarSign
} from 'lucide-vue-next';

import FinancialAppLayout from '@/layouts/FinancialAppLayout.vue';
import { useTranslation } from '@/composables/useTranslation';
import { useToast } from '@/composables/useToast';
import { Transaction, TransactionListResponse } from '@/types';
import formatCurrency from '@/composables/formatCurrency';

// Components
import TransactionSummaryCards from '@/pages/Transactions/Partials/SummaryCards.vue';
// import SearchModal from '@/pages/Transactions/Partials/SearchModal.vue';
import FilterModal from '@/pages/Transactions/Partials/FilterPanel.vue';
import TransactionCard from './Partials/TransactionCard.vue';
import SearchModal from './Partials/SearchModal.vue';

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

// Current month state
const currentMonth = ref(new Date().toISOString().slice(0, 7)); // YYYY-MM format
const searchQuery = ref('');
const currentFilters = ref({
  account_ids: [] as number[],
  category_ids: [] as number[],
  type: 'both' as string,
  flags: [] as string[],
  tag_ids: [] as number[],
  amount_min: undefined as number | undefined,
  amount_max: undefined as number | undefined,
});

// Computed
const currentMonthLabel = computed(() => {
  const date = new Date(currentMonth.value + '-01');
  return date.toLocaleDateString('id-ID', { 
    month: 'long', 
    year: 'numeric' 
  });
});

const hasActiveFilters = computed(() => {
  return !!(
    searchQuery.value ||
    currentFilters.value.account_ids.length > 0 ||
    currentFilters.value.category_ids.length > 0 ||
    currentFilters.value.tag_ids.length > 0 ||
    currentFilters.value.type !== 'both' ||
    currentFilters.value.flags.length > 0 ||
    currentFilters.value.amount_min ||
    currentFilters.value.amount_max
  );
});

// Monthly summary computed
const currentMonthSummary = computed(() => {
  // Filter transactions for current month
  const monthTransactions = props.transactions.data.filter(transaction => {
    const transactionMonth = new Date(transaction.date).toISOString().slice(0, 7);
    return transactionMonth === currentMonth.value;
  });

  const totalIncome = monthTransactions
    .filter(t => t.type === 'income')
    .reduce((sum, t) => sum + t.amount, 0);

  const totalExpense = monthTransactions
    .filter(t => t.type === 'expense')
    .reduce((sum, t) => sum + t.amount, 0);

  return {
    total_income: totalIncome,
    total_expense: totalExpense,
    net_amount: totalIncome - totalExpense,
    transaction_count: monthTransactions.length,
    period_label: currentMonthLabel.value,
  };
});

// Grouped transactions by date
const dailyGroups = computed(() => {
  // Filter transactions for current month and applied filters
  let filteredTransactions = props.transactions.data.filter(transaction => {
    const transactionMonth = new Date(transaction.date).toISOString().slice(0, 7);
    if (transactionMonth !== currentMonth.value) return false;

    // Apply search filter
    if (searchQuery.value) {
      const searchLower = searchQuery.value.toLowerCase();
      const matchesNote = transaction.note?.toLowerCase().includes(searchLower);
      const matchesAccount = transaction.account?.name?.toLowerCase().includes(searchLower);
      const matchesCategory = transaction.category?.name?.toLowerCase().includes(searchLower);
      if (!matchesNote && !matchesAccount && !matchesCategory) return false;
    }

    // Apply other filters
    if (currentFilters.value.type !== 'both' && transaction.type !== currentFilters.value.type) return false;
    if (currentFilters.value.account_ids.length > 0 && !currentFilters.value.account_ids.includes(transaction.account_id!)) return false;
    if (currentFilters.value.category_ids.length > 0 && !currentFilters.value.category_ids.includes(transaction.transaction_category_id!)) return false;
    if (currentFilters.value.flags.length > 0 && !currentFilters.value.flags.includes(transaction.flag)) return false;
    if (currentFilters.value.tag_ids.length > 0) {
      const transactionTagIds = transaction.tags?.map(tag => tag.id) || [];
      const hasMatchingTag = currentFilters.value.tag_ids.some(tagId => transactionTagIds.includes(tagId));
      if (!hasMatchingTag) return false;
    }
    if (currentFilters.value.amount_min && transaction.amount < currentFilters.value.amount_min) return false;
    if (currentFilters.value.amount_max && transaction.amount > currentFilters.value.amount_max) return false;

    return true;
  });

  // Group by date
  const groups: Record<string, any> = {};
  
  filteredTransactions.forEach(transaction => {
    const date = new Date(transaction.date).toISOString().slice(0, 10); // YYYY-MM-DD
    
    if (!groups[date]) {
      groups[date] = {
        date,
        dateLabel: new Date(date).toLocaleDateString('id-ID', {
          day: 'numeric',
          month: 'long',
          year: 'numeric'
        }),
        transactions: [],
        subtotal: { income: 0, expense: 0, net: 0 }
      };
    }
    
    groups[date].transactions.push(transaction);
    
    if (transaction.type === 'income') {
      groups[date].subtotal.income += transaction.amount;
    } else {
      groups[date].subtotal.expense += transaction.amount;
    }
  });

  // Calculate net and format
  Object.values(groups).forEach((group: any) => {
    group.subtotal.net = group.subtotal.income - group.subtotal.expense;
    group.subtotalFormatted = formatCurrency(Math.abs(group.subtotal.net)) + (group.subtotal.net >= 0 ? '' : '');
    group.subtotalPrefix = group.subtotal.net >= 0 ? '+' : '-';
  });

  // Sort by date (newest first)
  return Object.values(groups).sort((a: any, b: any) => b.date.localeCompare(a.date));
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
  const date = new Date(currentMonth.value + '-01');
  date.setMonth(date.getMonth() - 1);
  currentMonth.value = date.toISOString().slice(0, 7);
};

const nextMonth = () => {
  const date = new Date(currentMonth.value + '-01');
  date.setMonth(date.getMonth() + 1);
  currentMonth.value = date.toISOString().slice(0, 7);
};

const applySearchFilter = (query: string) => {
  searchQuery.value = query;
  closeSearchModal();
};

const applyFilters = (filters: any) => {
  currentFilters.value = { ...filters };
  closeFilterModal();
};

const clearAllFilters = () => {
  searchQuery.value = '';
  currentFilters.value = {
    account_ids: [],
    category_ids: [],
    type: 'both',
    flags: [],
    tag_ids: [],
    amount_min: undefined,
    amount_max: undefined,
  };
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

// Watch for URL changes
watch(() => currentFilters.value, (newFilters) => {
  // Update URL parameters when filters change
}, { deep: true });

onMounted(() => {
  // Initialize any needed data
});
</script>

<template>
  <FinancialAppLayout :showHeader="false" :showFab="false" containerClass="px-0">
    <Head :title="pageTitle" />
    
    <!-- Header Section with Title and Action Buttons -->
    <div class="bg-gradient-to-r from-teal-500 to-teal-600 dark:from-teal-600 dark:to-teal-700 rounded-b-[24px] px-4 pt-4 pb-6 mb-4 shadow-lg dark:shadow-2xl">
      <!-- Header: Title + Search + Filter Buttons -->
      <div class="flex items-center justify-between mb-4">
        <div class="flex-1 min-w-0">
          <h1 class="text-xl font-semibold text-white truncate">{{ pageTitle }}</h1>
          <p class="text-sm text-teal-100 dark:text-teal-200 truncate">{{ currentMonthLabel }}</p>
        </div>
        
        <div class="flex items-center gap-2 ml-3">
          <button
            @click="openSearchModal"
            class="p-3 bg-white/20 hover:bg-white/30 rounded-xl text-white transition-colors duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center"
            title="Cari"
          >
            <Search class="w-5 h-5" />
          </button>
          
          <button
            @click="openFilterModal"
            :class="hasActiveFilters ? 'bg-white/30 ring-2 ring-white/50' : 'bg-white/20 hover:bg-white/30'"
            class="p-3 rounded-xl text-white transition-colors duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center relative"
            title="Filter"
          >
            <Filter class="w-5 h-5" />
            <div v-if="hasActiveFilters" class="absolute -top-1 -right-1 w-3 h-3 bg-white rounded-full"></div>
          </button>
        </div>
      </div>

      <!-- Month Navigation -->
      <div class="flex items-center justify-between mb-4 bg-white/10 rounded-xl p-3">
        <button
          @click="previousMonth"
          class="p-2 hover:bg-white/20 rounded-lg text-white transition-colors duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center"
        >
          <ChevronLeft class="w-5 h-5" />
        </button>
        
        <div class="flex items-center gap-2 text-white">
          <Calendar class="w-4 h-4" />
          <span class="font-medium">{{ currentMonthLabel }}</span>
        </div>
        
        <button
          @click="nextMonth"
          class="p-2 hover:bg-white/20 rounded-lg text-white transition-colors duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center"
        >
          <ChevronRight class="w-5 h-5" />
        </button>
      </div>

      <!-- 3 Summary Cards for Current Month -->
      <!-- <TransactionSummaryCards :summary="currentMonthSummary" /> -->
    </div>

    <!-- Main Content: Daily Grouped Transactions -->
    <div class="px-4">
      <!-- Grouped Transaction List -->
      <div v-if="dailyGroups.length > 0" class="space-y-4">
        <div 
          v-for="group in dailyGroups" 
          :key="group.date"
          class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden"
        >
          <!-- Date Header with Subtotal -->
          <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <Calendar class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ group.dateLabel }}
                </span>
              </div>
              
              <div class="text-right">
                <span 
                  class="text-sm font-semibold"
                  :class="group.subtotal.net >= 0 
                    ? 'text-teal-600 dark:text-teal-400' 
                    : 'text-coral-600 dark:text-coral-400'"
                >
                  {{ group.subtotalPrefix }}Rp {{ formatCurrency(Math.abs(group.subtotal.net), 'decimal') }}
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
            />
          </div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-else class="text-center py-12 px-4">
        <div class="text-gray-400 mb-6">
          <DollarSign class="w-16 h-16 mx-auto mb-4 opacity-50" />
          <p class="text-lg font-medium">Tidak ada transaksi</p>
          <p class="text-sm text-gray-500 mt-1">
            {{ hasActiveFilters 
              ? 'Tidak ada transaksi yang sesuai dengan filter yang dipilih' 
              : `Belum ada transaksi di ${currentMonthLabel}` 
            }}
          </p>
        </div>
        
        <div class="flex flex-col gap-3">
          <button
            v-if="hasActiveFilters"
            @click="clearAllFilters"
            class="w-full py-3 text-teal-600 border border-teal-600 rounded-xl hover:bg-teal-50 dark:hover:bg-teal-900/20 transition-colors duration-200 font-medium"
          >
            Hapus Filter
          </button>
          
          <Link
            href="/transactions/create"
            class="w-full py-3 bg-teal-500 text-white rounded-xl hover:bg-teal-600 transition-colors duration-200 font-medium text-center"
          >
            Tambah Transaksi
          </Link>
        </div>
      </div>
    </div>

    <!-- Search Modal -->
    <SearchModal 
      v-if="showSearchModal" 
      :initial-query="searchQuery"
      @apply="applySearchFilter"
      @close="closeSearchModal" 
    />
    
    <!-- Filter Modal -->
    <FilterModal 
      v-if="showFilterModal"
      :filters="currentFilters"
      :master-data="transactions.master_data"
      @apply="applyFilters"
      @close="closeFilterModal" 
    />
  </FinancialAppLayout>
</template>
