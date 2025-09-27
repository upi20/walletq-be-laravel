<script setup lang="ts">
import { ref, computed } from 'vue';
import { 
  ChevronUp, 
  ChevronDown, 
  MoreVertical, 
  Edit, 
  Eye, 
  Trash2,
  CreditCard,
  TrendingUp,
  TrendingDown,
  ArrowRightLeft,
  Receipt,
  Tag as TagIcon,
  Calendar,
  DollarSign
} from 'lucide-vue-next';
import { Transaction } from '@/types';

interface Props {
  transactions: Transaction[];
  loading?: boolean;
  selectedTransactions?: number[];
  currentSort?: {
    by?: string;
    order?: string;
  };
}

const props = defineProps<Props>();

const emit = defineEmits<{
  sortChange: [sort: { sort_by: string; sort_order: string }];
  selectionChange: [selected: number[]];
  action: [action: string, transactionId: number];
}>();

// State
const activeMenu = ref<number | null>(null);

// Computed
const sortedColumns = ['date', 'amount', 'account', 'category'];

// Methods
const toggleSort = (column: string) => {
  let newOrder = 'desc';
  
  if (props.currentSort?.by === column) {
    newOrder = props.currentSort.order === 'desc' ? 'asc' : 'desc';
  }
  
  emit('sortChange', {
    sort_by: column,
    sort_order: newOrder
  });
};

const getSortIcon = (column: string) => {
  if (props.currentSort?.by !== column) return null;
  return props.currentSort.order === 'desc' ? ChevronDown : ChevronUp;
};

const toggleMenu = (transactionId: number) => {
  activeMenu.value = activeMenu.value === transactionId ? null : transactionId;
};

const handleAction = (action: string, transactionId: number) => {
  emit('action', action, transactionId);
  activeMenu.value = null;
};

const toggleSelection = (transactionId: number) => {
  const currentSelection = props.selectedTransactions || [];
  const newSelection = currentSelection.includes(transactionId)
    ? currentSelection.filter(id => id !== transactionId)
    : [...currentSelection, transactionId];
  
  emit('selectionChange', newSelection);
};

const selectAll = () => {
  const allIds = props.transactions.map(t => t.id);
  const isAllSelected = allIds.every(id => props.selectedTransactions?.includes(id));
  
  emit('selectionChange', isAllSelected ? [] : allIds);
};

const isSelected = (transactionId: number) => {
  return props.selectedTransactions?.includes(transactionId) || false;
};

const isAllSelected = computed(() => {
  if (!props.transactions.length) return false;
  return props.transactions.every(t => props.selectedTransactions?.includes(t.id));
});

const isIndeterminate = computed(() => {
  const selectedCount = props.selectedTransactions?.length || 0;
  return selectedCount > 0 && selectedCount < props.transactions.length;
});

// Format helpers
const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('Rp', 'Rp ');
};

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  });
};

const getTransactionIcon = (transaction: Transaction) => {
  switch (transaction.flag) {
    case 'transfer_in':
    case 'transfer_out':
      return ArrowRightLeft;
    case 'debt_payment':
    case 'debt_collect':
      return Receipt;
    default:
      return transaction.type === 'income' ? TrendingUp : TrendingDown;
  }
};

const getAmountColor = (transaction: Transaction) => {
  return transaction.type === 'income' 
    ? 'text-teal-600 dark:text-teal-400'
    : 'text-coral-600 dark:text-coral-400';
};

const getFlagBadgeColor = (flag: string) => {
  switch (flag) {
    case 'transfer_in':
    case 'transfer_out':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
    case 'debt_payment':
    case 'debt_collect':
      return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
    case 'initial_balance':
      return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
  }
};
</script>

<template>
  <div class="relative">
    <!-- Loading Overlay -->
    <div v-if="loading" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm z-10 flex items-center justify-center rounded-xl">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-teal-600"></div>
    </div>

    <!-- Mobile Card Layout -->
    <div class="space-y-3">
      <!-- Select All Header -->
      <div v-if="transactions.length > 0" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <label class="flex items-center gap-3 cursor-pointer">
          <input
            type="checkbox"
            :checked="isAllSelected"
            :indeterminate="isIndeterminate"
            @change="selectAll"
            class="text-teal-600 border-gray-300 dark:border-gray-600 rounded focus:ring-teal-500"
          />
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ selectedTransactions?.length || 0 }} dari {{ transactions.length }} dipilih
          </span>
        </label>
        
        <!-- Sort Options -->
        <div class="flex items-center gap-2">
          <button
            @click="toggleSort('date')"
            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
            title="Urutkan berdasarkan tanggal"
          >
            <Calendar class="w-4 h-4" />
            <component 
              :is="getSortIcon('date')" 
              v-if="getSortIcon('date')" 
              class="w-3 h-3 ml-1" 
            />
          </button>
          
          <button
            @click="toggleSort('amount')"
            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
            title="Urutkan berdasarkan jumlah"
          >
            <DollarSign class="w-4 h-4" />
            <component 
              :is="getSortIcon('amount')" 
              v-if="getSortIcon('amount')" 
              class="w-3 h-3 ml-1" 
            />
          </button>
        </div>
      </div>

      <!-- Transaction Cards -->
      <div 
        v-for="transaction in transactions" 
        :key="transaction.id"
        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 transition-all duration-200 hover:shadow-md"
        :class="isSelected(transaction.id) ? 'ring-2 ring-teal-500 border-teal-500' : ''"
      >
        <!-- Card Header -->
        <div class="flex items-start gap-3 mb-3">
          <!-- Checkbox -->
          <input
            type="checkbox"
            :checked="isSelected(transaction.id)"
            @change="toggleSelection(transaction.id)"
            class="mt-1 text-teal-600 border-gray-300 dark:border-gray-600 rounded focus:ring-teal-500"
          />
          
          <!-- Transaction Icon -->
          <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center"
               :class="transaction.type === 'income' 
                 ? 'bg-teal-100 dark:bg-teal-900' 
                 : 'bg-coral-100 dark:bg-coral-900'">
            <component 
              :is="getTransactionIcon(transaction)" 
              class="w-6 h-6"
              :class="transaction.type === 'income' 
                ? 'text-teal-600 dark:text-teal-400' 
                : 'text-coral-600 dark:text-coral-400'"
            />
          </div>

          <!-- Main Content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between">
              <div class="flex-1 min-w-0">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                  {{ transaction.note || 'Transaksi ' + (transaction.type === 'income' ? 'Pemasukan' : 'Pengeluaran') }}
                </h3>
                
                <div class="flex items-center gap-2 mt-1">
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatDate(transaction.date) }}
                  </span>
                  
                  <!-- Flag Badge -->
                  <span 
                    v-if="transaction.flag !== 'normal'"
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                    :class="getFlagBadgeColor(transaction.flag)"
                  >
                    {{ transaction.flag_label }}
                  </span>
                </div>
              </div>
              
              <!-- Amount -->
              <div class="text-right ml-3 flex-shrink-0">
                <div class="text-lg font-bold" :class="getAmountColor(transaction)">
                  {{ transaction.formatted_amount }}
                </div>
              </div>
            </div>
          </div>
          
          <!-- Action Menu -->
          <div class="relative flex-shrink-0">
            <button
              @click="toggleMenu(transaction.id)"
              class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
            >
              <MoreVertical class="w-4 h-4" />
            </button>

            <!-- Dropdown Menu -->
            <div 
              v-if="activeMenu === transaction.id"
              class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-20"
            >
              <div class="py-1">
                <button
                  @click="handleAction('view', transaction.id)"
                  class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                >
                  <Eye class="w-4 h-4" />
                  Lihat Detail
                </button>
                
                <button
                  @click="handleAction('edit', transaction.id)"
                  class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                >
                  <Edit class="w-4 h-4" />
                  Edit
                </button>
                
                <hr class="my-1 border-gray-200 dark:border-gray-600" />
                
                <button
                  @click="handleAction('delete', transaction.id)"
                  class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200"
                >
                  <Trash2 class="w-4 h-4" />
                  Hapus
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Card Footer -->
        <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
          <!-- Account Info -->
          <div class="flex items-center gap-2">
            <CreditCard class="w-4 h-4 text-gray-400" />
            <div class="text-xs text-gray-600 dark:text-gray-400">
              <span class="font-medium">{{ transaction.account?.name || 'Akun Tidak Diketahui' }}</span>
              <span v-if="transaction.account?.category" class="text-gray-500"> • {{ transaction.account.category.name }}</span>
            </div>
          </div>
          
          <!-- Category -->
          <div class="flex items-center gap-2">
            <div 
              class="w-3 h-3 rounded-full flex-shrink-0"
              :class="transaction.category?.type === 'income' 
                ? 'bg-teal-500' 
                : 'bg-coral-500'"
            ></div>
            <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">
              {{ transaction.category?.name || 'Kategori Tidak Diketahui' }}
            </span>
          </div>
        </div>
        
        <!-- Tags -->
        <div v-if="transaction.tags && transaction.tags.length > 0" class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
          <TagIcon class="w-4 h-4 text-gray-400" />
          <div class="flex flex-wrap gap-1">
            <span 
              v-for="tag in transaction.tags" 
              :key="tag.id"
              class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300"
            >
              {{ tag.name }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State - Mobile Optimized -->
    <div v-if="!transactions.length && !loading" class="text-center py-8 px-4">
      <div class="text-gray-400 mb-4">
        <Receipt class="w-12 h-12 mx-auto mb-3 opacity-50" />
        <p class="text-base font-medium">Tidak ada transaksi</p>
        <p class="text-sm text-gray-500 mt-1">Belum ada transaksi yang ditampilkan</p>
      </div>
    </div>
  </div>

  <!-- Click outside to close menu -->
  <div 
    v-if="activeMenu" 
    @click="activeMenu = null" 
    class="fixed inset-0 z-10"
  ></div>
</template>

<style scoped>
/* Custom checkbox styles */
input[type="checkbox"]:indeterminate {
  background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M4 8h8'/%3e%3c/svg%3e");
}
</style>
