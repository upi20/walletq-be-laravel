<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { 
  Plus, 
  Search, 
  Filter,
  Download,
  TrendingUp,
  TrendingDown,
  DollarSign,
  Calendar,
  ChevronLeft,
  ChevronRight,
  MoreVertical,
  Edit,
  Trash2,
  Eye
} from 'lucide-vue-next';

import FinancialAppLayout from '@/layouts/FinancialAppLayout.vue';
import { useTranslation } from '@/composables/useTranslation';
import { useToast } from '@/composables/useToast';
import { Transaction, TransactionListResponse } from '@/types';

// Components
import TransactionFilterPanel from '@/pages/Transactions/Partials/FilterPanel.vue';
import TransactionSummaryCards from '@/pages/Transactions/Partials/SummaryCards.vue';
import TransactionTable from '@/pages/Transactions/Partials/TransactionTable.vue';
import QuickFilters from '@/pages/Transactions/Partials/QuickFilters.vue';

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
const showFilterPanel = ref(false);
const selectedTransactions = ref<number[]>([]);

// Current filters
const currentFilters = ref({
  ...props.transactions.filters
});

// Computed
const hasActiveFilters = computed(() => {
  const filters = currentFilters.value;
  return !!(
    filters.search ||
    filters.date_from ||
    filters.date_to ||
    (filters.account_ids && filters.account_ids.length > 0) ||
    (filters.category_ids && filters.category_ids.length > 0) ||
    (filters.tag_ids && filters.tag_ids.length > 0) ||
    filters.type !== 'both' ||
    (filters.flags && filters.flags.length > 0) ||
    filters.amount_min ||
    filters.amount_max
  );
});

const totalPages = computed(() => props.transactions.meta.last_page);
const currentPage = computed(() => props.transactions.meta.current_page);

// Methods
const applyFilters = (newFilters: any) => {
  loading.value = true;
  
  // Clean up empty values
  const cleanFilters = Object.fromEntries(
    Object.entries(newFilters).filter(([_, value]) => {
      if (Array.isArray(value)) return value.length > 0;
      return value !== null && value !== undefined && value !== '';
    })
  );

  router.get('/transactions/data', cleanFilters as any, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      currentFilters.value = { ...cleanFilters };
      loading.value = false;
    },
    onError: () => {
      error('Gagal memuat data transaksi');
      loading.value = false;
    }
  });
};

const clearFilters = () => {
  const defaultFilters = {
    type: 'both',
    sort_by: 'date',
    sort_order: 'desc',
    per_page: 25,
    page: 1
  };
  
  applyFilters(defaultFilters);
  showFilterPanel.value = false;
};

const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    applyFilters({ ...currentFilters.value, page });
  }
};

const exportTransactions = () => {
  loading.value = true;
  
  router.get('/transactions/export', currentFilters.value as any, {
    onSuccess: () => {
      success('Export berhasil diunduh');
      loading.value = false;
    },
    onError: () => {
      error('Gagal mengekspor data');
      loading.value = false;
    }
  });
};

const handleTransactionAction = (action: string, transactionId: number) => {
  switch (action) {
    case 'view':
      // TODO: Implement view transaction
      info('Fitur lihat detail akan segera hadir');
      break;
    case 'edit':
      // TODO: Implement edit transaction
      info('Fitur edit transaksi akan segera hadir');
      break;
    case 'delete':
      // TODO: Implement delete transaction
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
  <FinancialAppLayout :showHeader="false" :showFab="false">
    <Head :title="pageTitle" />
    
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-teal-500 to-teal-600 dark:from-teal-600 dark:to-teal-700 rounded-b-[32px] px-6 pt-6 pb-8 mb-6 shadow-lg dark:shadow-2xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-semibold text-white">{{ pageTitle }}</h1>
          <p class="text-teal-100 dark:text-teal-200">{{ transactions.summary.period_label }}</p>
        </div>
        
        <div class="flex items-center gap-3">
          <button
            @click="exportTransactions"
            :disabled="loading"
            class="p-3 bg-white/20 hover:bg-white/30 rounded-xl text-white transition-colors duration-200"
          >
            <Download class="w-5 h-5" />
          </button>
          
          <Link
            href="/transactions/create"
            class="flex items-center gap-2 px-4 py-2 bg-white text-teal-600 rounded-xl font-medium hover:bg-teal-50 transition-colors duration-200"
          >
            <Plus class="w-5 h-5" />
            <span>Tambah</span>
          </Link>
        </div>
      </div>
      
      <!-- Summary Cards -->
      <TransactionSummaryCards 
        :summary="transactions.summary"
        :quick-stats="transactions.quick_stats"
      />
    </div>

    <!-- Main Content -->
    <div class="px-6">
      <!-- Quick Filters & Search -->
      <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <QuickFilters 
          :current-preset="currentFilters.date_preset"
          @apply-preset="(preset) => applyFilters({ ...currentFilters, date_preset: preset })"
        />
        
        <div class="flex-1 flex gap-3">
          <!-- Search Input -->
          <div class="relative flex-1">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
            <input
              v-model="currentFilters.search"
              @input="applyFilters(currentFilters)"
              type="text"
              placeholder="Cari transaksi..."
              class="w-full pl-10 pr-4 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            />
          </div>
          
          <!-- Filter Toggle -->
          <button
            @click="showFilterPanel = !showFilterPanel"
            class="flex items-center gap-2 px-4 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
            :class="{ 'ring-2 ring-teal-500 border-teal-500': hasActiveFilters }"
          >
            <Filter class="w-4 h-4" />
            <span>Filter</span>
            <div v-if="hasActiveFilters" class="w-2 h-2 bg-teal-500 rounded-full"></div>
          </button>
        </div>
      </div>

      <!-- Filter Panel -->
      <TransactionFilterPanel
        v-if="showFilterPanel"
        :filters="currentFilters"
        :master-data="transactions.master_data"
        :loading="loading"
        @apply-filters="applyFilters"
        @clear-filters="clearFilters"
        @close="showFilterPanel = false"
        class="mb-6"
      />

      <!-- Transaction Table -->
      <div class="mb-6">
        <TransactionTable
          :transactions="transactions.data"
          :loading="loading"
          :selected-transactions="selectedTransactions"
          :current-sort="{ by: currentFilters.sort_by, order: currentFilters.sort_order }"
          @sort-change="(sort) => applyFilters({ ...currentFilters, ...sort })"
          @selection-change="selectedTransactions = $event"
          @action="handleTransactionAction"
        />
      </div>

      <!-- Pagination -->
      <div v-if="transactions.meta.total > 0" class="flex items-center justify-between">
        <div class="text-sm text-gray-600 dark:text-gray-400">
          Menampilkan {{ transactions.meta.from }} - {{ transactions.meta.to }} 
          dari {{ transactions.meta.total }} transaksi
        </div>
        
        <div class="flex items-center gap-2">
          <button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage <= 1"
            class="p-2 border border-gray-200 dark:border-gray-700 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
          >
            <ChevronLeft class="w-4 h-4" />
          </button>
          
          <!-- Page Numbers -->
          <div class="flex items-center gap-1">
            <template v-for="page in Math.min(5, totalPages)" :key="page">
              <button
                @click="goToPage(page)"
                class="px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg transition-colors duration-200"
                :class="page === currentPage 
                  ? 'bg-teal-500 text-white border-teal-500' 
                  : 'hover:bg-gray-50 dark:hover:bg-gray-700'"
              >
                {{ page }}
              </button>
            </template>
          </div>
          
          <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage >= totalPages"
            class="p-2 border border-gray-200 dark:border-gray-700 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
          >
            <ChevronRight class="w-4 h-4" />
          </button>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <div class="text-gray-400 mb-4">
          <DollarSign class="w-16 h-16 mx-auto mb-4 opacity-50" />
          <p class="text-lg font-medium">Tidak ada transaksi</p>
          <p class="text-sm">Belum ada transaksi yang sesuai dengan filter yang dipilih</p>
        </div>
        
        <div class="flex justify-center gap-4">
          <button
            v-if="hasActiveFilters"
            @click="clearFilters"
            class="px-4 py-2 text-teal-600 border border-teal-600 rounded-lg hover:bg-teal-50 transition-colors duration-200"
          >
            Hapus Filter
          </button>
          
          <Link
            href="/transactions/create"
            class="px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors duration-200"
          >
            Tambah Transaksi Pertama
          </Link>
        </div>
      </div>
    </div>
  </FinancialAppLayout>
</template>
