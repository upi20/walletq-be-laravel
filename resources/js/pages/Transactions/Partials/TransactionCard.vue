<script setup lang="ts">
import { computed, ref } from 'vue';
import { 
  MoreVertical,
  TrendingUp,
  TrendingDown,
  ArrowRightLeft,
  Receipt,
  CreditCard,
  Tag as TagIcon,
  Eye,
  Edit,
  Trash2
} from 'lucide-vue-next';
import { Transaction } from '@/types';
import formatCurrency from '@/composables/formatCurrency';

interface Props {
  transaction: Transaction;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  action: [action: string, transactionId: number];
}>();

// State
const showActionMenu = ref(false);

// Computed
const transactionIcon = computed(() => {
  switch (props.transaction.flag) {
    case 'transfer_in':
    case 'transfer_out':
      return ArrowRightLeft;
    case 'debt_payment':
    case 'debt_collect':
      return Receipt;
    default:
      return props.transaction.type === 'income' ? TrendingUp : TrendingDown;
  }
});

const amountColor = computed(() => {
  return props.transaction.type === 'income' 
    ? 'text-teal-600 dark:text-teal-400'
    : 'text-coral-600 dark:text-coral-400';
});

const iconBgColor = computed(() => {
  return props.transaction.type === 'income' 
    ? 'bg-teal-100 dark:bg-teal-900' 
    : 'bg-coral-100 dark:bg-coral-900';
});

const iconColor = computed(() => {
  return props.transaction.type === 'income' 
    ? 'text-teal-600 dark:text-teal-400' 
    : 'text-coral-600 dark:text-coral-400';
});

const flagBadgeColor = computed(() => {
  switch (props.transaction.flag) {
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
});

const formattedAmount = computed(() => {
  const amount = formatCurrency(props.transaction.amount, 'decimal');
  const prefix = props.transaction.type === 'income' ? '+' : '-';
  return `${prefix}Rp ${amount}`;
});

// Methods
const toggleActionMenu = () => {
  showActionMenu.value = !showActionMenu.value;
};

const handleAction = (action: string) => {
  emit('action', action, props.transaction.id);
  showActionMenu.value = false;
};
</script>

<template>
  <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-200">
    <div class="flex items-start gap-3">
      <!-- Transaction Icon -->
      <div 
        class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center"
        :class="iconBgColor"
      >
        <component 
          :is="transactionIcon" 
          class="w-5 h-5"
          :class="iconColor"
        />
      </div>

      <!-- Main Content -->
      <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between">
          <div class="flex-1 min-w-0">
            <!-- Transaction Title -->
            <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
              {{ transaction.note || `Transaksi ${transaction.type === 'income' ? 'Pemasukan' : 'Pengeluaran'}` }}
            </h4>
            
            <!-- Account & Category Info -->
            <div class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
              <CreditCard class="w-3 h-3" />
              <span>{{ transaction.account?.name || 'Akun Tidak Diketahui' }}</span>
              
              <span class="text-gray-300 dark:text-gray-600">•</span>
              
              <div class="flex items-center gap-1">
                <div 
                  class="w-2 h-2 rounded-full"
                  :class="transaction.category?.type === 'income' ? 'bg-teal-500' : 'bg-coral-500'"
                ></div>
                <span>{{ transaction.category?.name || 'Kategori Tidak Diketahui' }}</span>
              </div>
            </div>

            <!-- Flag Badge & Tags -->
            <div class="flex items-center gap-2 mt-2">
              <!-- Flag Badge -->
              <span 
                v-if="transaction.flag !== 'normal'"
                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                :class="flagBadgeColor"
              >
                {{ transaction.flag }}
              </span>
              
              <!-- Tags -->
              <div v-if="transaction.tags && transaction.tags.length > 0" class="flex items-center gap-1">
                <TagIcon class="w-3 h-3 text-gray-400" />
                <div class="flex gap-1">
                  <span 
                    v-for="tag in transaction.tags.slice(0, 2)" 
                    :key="tag.id"
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                  >
                    {{ tag.name }}
                  </span>
                  <span 
                    v-if="transaction.tags.length > 2"
                    class="text-xs text-gray-500 dark:text-gray-400"
                  >
                    +{{ transaction.tags.length - 2 }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Amount & Action -->
          <div class="flex items-start gap-3 ml-3">
            <!-- Amount -->
            <div class="text-right">
              <div class="text-sm font-semibold" :class="amountColor">
                {{ formattedAmount }}
              </div>
            </div>
            
            <!-- Action Menu -->
            <div class="relative">
              <button
                @click="toggleActionMenu"
                class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
              >
                <MoreVertical class="w-4 h-4" />
              </button>

              <!-- Dropdown Menu -->
              <div 
                v-if="showActionMenu"
                class="absolute right-0 mt-1 w-40 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-20"
              >
                <div class="py-1">
                  <button
                    @click="handleAction('view')"
                    class="flex items-center gap-2 w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                  >
                    <Eye class="w-4 h-4" />
                    Lihat
                  </button>
                  
                  <button
                    @click="handleAction('edit')"
                    class="flex items-center gap-2 w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                  >
                    <Edit class="w-4 h-4" />
                    Edit
                  </button>
                  
                  <hr class="my-1 border-gray-200 dark:border-gray-600" />
                  
                  <button
                    @click="handleAction('delete')"
                    class="flex items-center gap-2 w-full px-3 py-2 text-sm text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200"
                  >
                    <Trash2 class="w-4 h-4" />
                    Hapus
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Click outside to close menu -->
  <div 
    v-if="showActionMenu" 
    @click="showActionMenu = false" 
    class="fixed inset-0 z-10"
  ></div>
</template>

<style scoped>
/* Ensure action menu appears above other content */
.z-20 {
  z-index: 20;
}

.z-10 {
  z-index: 10;
}
</style>
