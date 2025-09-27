<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { 
  X, 
  Calendar, 
  CreditCard, 
  FolderOpen, 
  Tag, 
  DollarSign,
  Filter,
  RotateCcw
} from 'lucide-vue-next';

interface Props {
  filters: {
    date_from?: string;
    date_to?: string;
    date_preset?: string;
    account_ids?: number[];
    category_ids?: number[];
    type?: string;
    flags?: string[];
    amount_min?: number;
    amount_max?: number;
    tag_ids?: number[];
    search?: string;
    sort_by?: string;
    sort_order?: string;
    per_page?: number;
  };
  masterData: {
    accounts: Array<{
      id: number;
      name: string;
      current_balance: number;
      formatted_balance: string;
      category?: { id: number; name: string; type: string; };
    }>;
    income_categories: Array<{ id: number; name: string; type: string; }>;
    expense_categories: Array<{ id: number; name: string; type: string; }>;
    tags: Array<{ id: number; name: string; }>;
    flag_options: Array<{ value: string; label: string; }>;
    type_options: Array<{ value: string; label: string; }>;
    sort_options: Array<{ value: string; label: string; }>;
    per_page_options: Array<{ value: number; label: string; }>;
  };
  loading?: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  applyFilters: [filters: any];
  clearFilters: [];
  close: [];
}>();

// Local filter state
const localFilters = ref({ ...props.filters });

// Categories based on type
const availableCategories = computed(() => {
  if (localFilters.value.type === 'income') {
    return props.masterData.income_categories;
  } else if (localFilters.value.type === 'expense') {
    return props.masterData.expense_categories;
  } else {
    return [...props.masterData.income_categories, ...props.masterData.expense_categories];
  }
});

// Handle multi-select
const toggleArrayValue = (array: number[] | undefined, value: number): number[] => {
  const currentArray = array || [];
  const index = currentArray.indexOf(value);
  
  if (index > -1) {
    return currentArray.filter(item => item !== value);
  } else {
    return [...currentArray, value];
  }
};

const toggleStringArrayValue = (array: string[] | undefined, value: string): string[] => {
  const currentArray = array || [];
  const index = currentArray.indexOf(value);
  
  if (index > -1) {
    return currentArray.filter(item => item !== value);
  } else {
    return [...currentArray, value];
  }
};

// Handle account selection
const toggleAccount = (accountId: number) => {
  localFilters.value.account_ids = toggleArrayValue(localFilters.value.account_ids, accountId);
};

// Handle category selection
const toggleCategory = (categoryId: number) => {
  localFilters.value.category_ids = toggleArrayValue(localFilters.value.category_ids, categoryId);
};

// Handle tag selection
const toggleTag = (tagId: number) => {
  localFilters.value.tag_ids = toggleArrayValue(localFilters.value.tag_ids, tagId);
};

// Handle flag selection
const toggleFlag = (flag: string) => {
  localFilters.value.flags = toggleStringArrayValue(localFilters.value.flags, flag);
};

// Apply filters
const applyFilters = () => {
  emit('applyFilters', { ...localFilters.value });
};

// Clear all filters
const clearFilters = () => {
  emit('clearFilters');
};

// Close panel
const closePanel = () => {
  emit('close');
};

// Watch for type changes to reset categories
watch(() => localFilters.value.type, () => {
  localFilters.value.category_ids = [];
});

// Format currency for display
const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID').format(amount);
};
</script>

<template>
  <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
    <!-- Header -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-2">
        <Filter class="w-5 h-5 text-teal-600 dark:text-teal-400" />
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filter Transaksi</h3>
      </div>
      
      <div class="flex items-center gap-2">
        <button
          @click="clearFilters"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
          title="Reset filter"
        >
          <RotateCcw class="w-4 h-4" />
        </button>
        
        <button
          @click="closePanel"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Filter Content -->
    <div class="p-4 space-y-6">
      <!-- Date Range -->
      <div>
        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
          <Calendar class="w-4 h-4" />
          Rentang Tanggal
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="text-xs text-gray-500 dark:text-gray-400">Dari</label>
            <input
              v-model="localFilters.date_from"
              type="date"
              class="w-full mt-1 px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="text-xs text-gray-500 dark:text-gray-400">Sampai</label>
            <input
              v-model="localFilters.date_to"
              type="date"
              class="w-full mt-1 px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            />
          </div>
        </div>
      </div>

      <!-- Transaction Type -->
      <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 block">
          Tipe Transaksi
        </label>
        <select
          v-model="localFilters.type"
          class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent"
        >
          <option 
            v-for="option in masterData.type_options" 
            :key="option.value" 
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </select>
      </div>

      <!-- Accounts -->
      <div>
        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
          <CreditCard class="w-4 h-4" />
          Akun
        </label>
        <div class="max-h-40 overflow-y-auto space-y-2 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
          <label
            v-for="account in masterData.accounts"
            :key="account.id"
            class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors duration-200"
          >
            <input
              type="checkbox"
              :checked="localFilters.account_ids?.includes(account.id)"
              @change="toggleAccount(account.id)"
              class="text-teal-600 border-gray-300 rounded focus:ring-teal-500"
            />
            <div class="flex-1">
              <div class="text-sm font-medium text-gray-900 dark:text-white">
                {{ account.name }}
              </div>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ account.formatted_balance }}
                <span v-if="account.category">• {{ account.category.name }}</span>
              </div>
            </div>
          </label>
        </div>
      </div>

      <!-- Categories -->
      <div>
        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
          <FolderOpen class="w-4 h-4" />
          Kategori
        </label>
        <div class="max-h-40 overflow-y-auto space-y-2 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
          <label
            v-for="category in availableCategories"
            :key="category.id"
            class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors duration-200"
          >
            <input
              type="checkbox"
              :checked="localFilters.category_ids?.includes(category.id)"
              @change="toggleCategory(category.id)"
              class="text-teal-600 border-gray-300 rounded focus:ring-teal-500"
            />
            <div class="text-sm text-gray-900 dark:text-white">
              {{ category.name }}
              <span 
                class="ml-2 text-xs px-2 py-1 rounded-full"
                :class="category.type === 'income' 
                  ? 'bg-teal-100 text-teal-700 dark:bg-teal-900 dark:text-teal-300'
                  : 'bg-coral-100 text-coral-700 dark:bg-coral-900 dark:text-coral-300'"
              >
                {{ category.type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
              </span>
            </div>
          </label>
        </div>
      </div>

      <!-- Tags -->
      <div>
        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
          <Tag class="w-4 h-4" />
          Tag
        </label>
        <div class="max-h-32 overflow-y-auto space-y-2 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
          <label
            v-for="tag in masterData.tags"
            :key="tag.id"
            class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors duration-200"
          >
            <input
              type="checkbox"
              :checked="localFilters.tag_ids?.includes(tag.id)"
              @change="toggleTag(tag.id)"
              class="text-teal-600 border-gray-300 rounded focus:ring-teal-500"
            />
            <span class="text-sm text-gray-900 dark:text-white">{{ tag.name }}</span>
          </label>
        </div>
      </div>

      <!-- Amount Range -->
      <div>
        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
          <DollarSign class="w-4 h-4" />
          Rentang Jumlah
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="text-xs text-gray-500 dark:text-gray-400">Minimal</label>
            <input
              v-model.number="localFilters.amount_min"
              type="number"
              min="0"
              step="1000"
              placeholder="0"
              class="w-full mt-1 px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="text-xs text-gray-500 dark:text-gray-400">Maksimal</label>
            <input
              v-model.number="localFilters.amount_max"
              type="number"
              min="0"
              step="1000"
              placeholder="Tidak terbatas"
              class="w-full mt-1 px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            />
          </div>
        </div>
      </div>

      <!-- Flags -->
      <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 block">
          Status Transaksi
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <label
            v-for="flag in masterData.flag_options"
            :key="flag.value"
            class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors duration-200"
          >
            <input
              type="checkbox"
              :checked="localFilters.flags?.includes(flag.value)"
              @change="toggleFlag(flag.value)"
              class="text-teal-600 border-gray-300 rounded focus:ring-teal-500"
            />
            <span class="text-sm text-gray-900 dark:text-white">{{ flag.label }}</span>
          </label>
        </div>
      </div>

      <!-- Sorting & Display -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
            Urutkan berdasarkan
          </label>
          <select
            v-model="localFilters.sort_by"
            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent"
          >
            <option 
              v-for="option in masterData.sort_options" 
              :key="option.value" 
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
        </div>
        
        <div>
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
            Urutan
          </label>
          <select
            v-model="localFilters.sort_order"
            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent"
          >
            <option value="desc">Menurun</option>
            <option value="asc">Menaik</option>
          </select>
        </div>
        
        <div>
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
            Per halaman
          </label>
          <select
            v-model.number="localFilters.per_page"
            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent"
          >
            <option 
              v-for="option in masterData.per_page_options" 
              :key="option.value" 
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="flex items-center justify-between p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
      <button
        @click="clearFilters"
        class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white transition-colors duration-200"
      >
        Reset Semua
      </button>
      
      <div class="flex gap-3">
        <button
          @click="closePanel"
          class="px-4 py-2 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200"
        >
          Batal
        </button>
        
        <button
          @click="applyFilters"
          :disabled="loading"
          class="px-6 py-2 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-lg hover:from-teal-600 hover:to-teal-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
        >
          {{ loading ? 'Memuat...' : 'Terapkan Filter' }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom scrollbar for overflow areas */
.max-h-40::-webkit-scrollbar,
.max-h-32::-webkit-scrollbar {
  width: 4px;
}

.max-h-40::-webkit-scrollbar-track,
.max-h-32::-webkit-scrollbar-track {
  background: transparent;
}

.max-h-40::-webkit-scrollbar-thumb,
.max-h-32::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 2px;
}

.max-h-40::-webkit-scrollbar-thumb:hover,
.max-h-32::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.8);
}
</style>
