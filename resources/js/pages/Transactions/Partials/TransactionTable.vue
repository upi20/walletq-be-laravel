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
  Calendar
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
  <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
    <!-- Loading Overlay -->
    <div v-if="loading" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm z-10 flex items-center justify-center">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-teal-600"></div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full">
        <!-- Header -->
        <thead class="bg-gray-50 dark:bg-gray-700/50">
          <tr>
            <th class="w-12 px-4 py-3">
              <input
                type="checkbox"
                :checked="isAllSelected"
                :indeterminate="isIndeterminate"
                @change="selectAll"
                class="text-teal-600 border-gray-300 dark:border-gray-600 rounded focus:ring-teal-500"
              />
            </th>
            
            <th 
              v-for="column in [
                { key: 'date', label: 'Tanggal', sortable: true },
                { key: 'description', label: 'Deskripsi', sortable: false },
                { key: 'account', label: 'Akun', sortable: true },
                { key: 'category', label: 'Kategori', sortable: true },
                { key: 'amount', label: 'Jumlah', sortable: true },
                { key: 'actions', label: '', sortable: false }
              ]"
              :key="column.key"
              class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              :class="column.sortable ? 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 select-none' : ''"
              @click="column.sortable ? toggleSort(column.key) : null"
            >
              <div class="flex items-center gap-2">
                <span>{{ column.label }}</span>
                <component 
                  :is="getSortIcon(column.key)" 
                  v-if="column.sortable && getSortIcon(column.key)" 
                  class="w-4 h-4" 
                />
              </div>
            </th>
          </tr>
        </thead>

        <!-- Body -->
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr 
            v-for="transaction in transactions" 
            :key="transaction.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200"
            :class="isSelected(transaction.id) ? 'bg-teal-50 dark:bg-teal-900/20' : ''"
          >
            <!-- Checkbox -->
            <td class="px-4 py-4">
              <input
                type="checkbox"
                :checked="isSelected(transaction.id)"
                @change="toggleSelection(transaction.id)"
                class="text-teal-600 border-gray-300 dark:border-gray-600 rounded focus:ring-teal-500"
              />
            </td>

            <!-- Date -->
            <td class="px-4 py-4">
              <div class="flex items-center gap-2">
                <Calendar class="w-4 h-4 text-gray-400" />
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ formatDate(transaction.date) }}
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ new Date(transaction.date).toLocaleDateString('id-ID', { weekday: 'short' }) }}
                  </div>
                </div>
              </div>
            </td>

            <!-- Description -->
            <td class="px-4 py-4">
              <div class="flex items-start gap-3">
                <!-- Icon -->
                <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
                     :class="transaction.type === 'income' 
                       ? 'bg-teal-100 dark:bg-teal-900' 
                       : 'bg-coral-100 dark:bg-coral-900'">
                  <component 
                    :is="getTransactionIcon(transaction)" 
                    class="w-5 h-5"
                    :class="transaction.type === 'income' 
                      ? 'text-teal-600 dark:text-teal-400' 
                      : 'text-coral-600 dark:text-coral-400'"
                  />
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                    {{ transaction.note || 'Transaksi ' + (transaction.type === 'income' ? 'Pemasukan' : 'Pengeluaran') }}
                  </div>
                  
                  <!-- Flag Badge -->
                  <div class="flex items-center gap-2 mt-1">
                    <span 
                      v-if="transaction.flag !== 'normal'"
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                      :class="getFlagBadgeColor(transaction.flag)"
                    >
                      {{ transaction.flag_label }}
                    </span>
                    
                    <!-- Tags -->
                    <div v-if="transaction.tags && transaction.tags.length > 0" class="flex items-center gap-1">
                      <TagIcon class="w-3 h-3 text-gray-400" />
                      <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ transaction.tags.map(tag => tag.name).join(', ') }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </td>

            <!-- Account -->
            <td class="px-4 py-4">
              <div class="flex items-center gap-2">
                <CreditCard class="w-4 h-4 text-gray-400" />
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ transaction.account?.name || 'Akun Tidak Diketahui' }}
                  </div>
                  <div v-if="transaction.account?.category" class="text-xs text-gray-500 dark:text-gray-400">
                    {{ transaction.account.category.name }}
                  </div>
                </div>
              </div>
            </td>

            <!-- Category -->
            <td class="px-4 py-4">
              <div class="flex items-center gap-2">
                <div 
                  class="w-3 h-3 rounded-full flex-shrink-0"
                  :class="transaction.category?.type === 'income' 
                    ? 'bg-teal-500' 
                    : 'bg-coral-500'"
                ></div>
                <span class="text-sm text-gray-900 dark:text-white">
                  {{ transaction.category?.name || 'Kategori Tidak Diketahui' }}
                </span>
              </div>
            </td>

            <!-- Amount -->
            <td class="px-4 py-4">
              <div class="text-right">
                <div class="text-sm font-semibold" :class="getAmountColor(transaction)">
                  {{ transaction.formatted_amount }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatCurrency(transaction.amount) }}
                </div>
              </div>
            </td>

            <!-- Actions -->
            <td class="px-4 py-4 text-right">
              <div class="relative">
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
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-if="!transactions.length && !loading" class="p-12 text-center">
      <div class="text-gray-400 mb-4">
        <Receipt class="w-16 h-16 mx-auto mb-4 opacity-50" />
        <p class="text-lg font-medium">Tidak ada transaksi</p>
        <p class="text-sm">Belum ada transaksi yang ditampilkan</p>
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
