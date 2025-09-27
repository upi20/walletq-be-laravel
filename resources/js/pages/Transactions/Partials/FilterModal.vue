<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { 
  X, 
  Filter,
  RotateCcw,
  CreditCard,
  FolderOpen,
  Tag as TagIcon,
  DollarSign,
  Calendar,
  Search
} from 'lucide-vue-next';

interface Props {
  filters: {
    period?: string;
    date_from?: string;
    date_to?: string;
    month?: string;
    year?: string;
    type?: string;
    account_ids?: number[];
    category_ids?: number[];
    tag_ids?: number[];
    flags?: string[];
    search?: string;
    amount_min?: number;
    amount_max?: number;
  };
  masterData: {
    accounts: Array<{
      id: number;
      name: string;
      current_balance: number;
      category?: { id: number; name: string; type: string; } | null;
    }>;
    income_categories: Array<{ id: number; name: string; type: string; }>;
    expense_categories: Array<{ id: number; name: string; type: string; }>;
    tags: Array<{ id: number; name: string; }>;
    flag_options: Array<{ value: string; label: string; }>;
    type_options: Array<{ value: string; label: string; }>;
    period_options: Array<{ value: string; label: string; }>;
  }|null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  apply: [filters: any];
  close: [];
}>();

// Local filter state
const localFilters = ref({
  period: 'month',
  type: 'both',
  account_ids: [],
  category_ids: [],
  tag_ids: [],
  flags: [],
  month: new Date().toISOString().slice(0, 7), // YYYY-MM format
  year: new Date().getFullYear().toString(),
  date_from: '',
  date_to: '',
  search: '',
  amount_min: undefined,
  amount_max: undefined,
  ...props.filters
});

// Watch for props changes and update local filters
watch(() => props.filters, (newFilters) => {
  localFilters.value = {
    period: 'month',
    type: 'both',
    account_ids: [],
    category_ids: [],
    tag_ids: [],
    flags: [],
    month: new Date().toISOString().slice(0, 7),
    year: new Date().getFullYear().toString(),
    date_from: '',
    date_to: '',
    search: '',
    amount_min: undefined,
    amount_max: undefined,
    ...newFilters
  };
}, { immediate: true, deep: true });

// Available categories based on type
const availableCategories = computed(() => {
  if (localFilters.value.type === 'income') {
    return props.masterData.income_categories;
  } else if (localFilters.value.type === 'expense') {
    return props.masterData.expense_categories;
  } else {
    return [...props.masterData.income_categories, ...props.masterData.expense_categories];
  }
});

// Helper functions for multi-select
const toggleArrayValue = (array: number[], value: number): number[] => {
  const index = array.indexOf(value);
  if (index > -1) {
    return array.filter(item => item !== value);
  } else {
    return [...array, value];
  }
};

const toggleStringArrayValue = (array: string[], value: string): string[] => {
  const index = array.indexOf(value);
  if (index > -1) {
    return array.filter(item => item !== value);
  } else {
    return [...array, value];
  }
};

// Toggle methods
const toggleAccount = (accountId: number) => {
  localFilters.value.account_ids = toggleArrayValue(localFilters.value.account_ids || [], accountId);
};

const toggleCategory = (categoryId: number) => {
  localFilters.value.category_ids = toggleArrayValue(localFilters.value.category_ids || [], categoryId);
};

const toggleTag = (tagId: number) => {
  localFilters.value.tag_ids = toggleArrayValue(localFilters.value.tag_ids || [], tagId);
};

const toggleFlag = (flag: string) => {
  localFilters.value.flags = toggleStringArrayValue(localFilters.value.flags || [], flag);
};

// Actions
const applyFilters = () => {
  emit('apply', { ...localFilters.value });
  emit('close');
};

const resetFilters = () => {
  localFilters.value = {
    account_ids: [],
    category_ids: [],
    type: 'both',
    flags: [],
    tag_ids: [],
    amount_min: undefined,
    amount_max: undefined,
  };
};

const closeModal = () => {
  emit('close');
};

// Watch for type changes to reset categories
watch(() => localFilters.value.type, () => {
  localFilters.value.category_ids = [];
});
</script>

<template>
  <!-- Modal Overlay -->
  <div 
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    @click.self="closeModal"
  >
    <!-- Modal Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg mx-auto border border-gray-200 dark:border-gray-700 max-h-[90vh] flex flex-col">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
        <div class="flex items-center gap-2">
          <Filter class="w-5 h-5 text-teal-600 dark:text-teal-400" />
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filter Transaksi</h3>
        </div>
        
        <div class="flex items-center gap-1">
          <button
            @click="resetFilters"
            class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center"
            title="Reset filter"
          >
            <RotateCcw class="w-4 h-4" />
          </button>
          
          <button
            @click="closeModal"
            class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Filter Content - Scrollable -->
      <div class="flex-1 overflow-y-auto p-4 space-y-5">
        <!-- Period Selection -->
        <div>
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 block">
            Periode
          </label>
          <select
            v-model="localFilters.period"
            class="w-full px-3 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm"
          >
            <option 
              v-for="option in masterData.period_options" 
              :key="option.value" 
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- Custom Date Range (show when period is custom) -->
        <div v-if="localFilters.period === 'custom'">
          <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            <Calendar class="w-4 h-4" />
            Rentang Tanggal Kustom
          </label>
          <div class="space-y-3">
            <div>
              <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">Dari Tanggal</label>
              <input
                v-model="localFilters.date_from"
                type="date"
                class="w-full px-3 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm"
              />
            </div>
            <div>
              <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">Sampai Tanggal</label>
              <input
                v-model="localFilters.date_to"
                type="date"
                class="w-full px-3 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm"
              />
            </div>
          </div>
        </div>

        <!-- Month Selection (show when period is month) -->
        <div v-if="localFilters.period === 'month'">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 block">
            Pilih Bulan
          </label>
          <input
            v-model="localFilters.month"
            type="month"
            class="w-full px-3 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm"
          />
        </div>

        <!-- Year Selection (show when period is year) -->
        <div v-if="localFilters.period === 'year'">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 block">
            Pilih Tahun
          </label>
          <input
            v-model="localFilters.year"
            type="number"
            min="2020"
            max="2030"
            class="w-full px-3 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm"
          />
        </div>

        <!-- Search -->
        <div>
          <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            <Search class="w-4 h-4" />
            Pencarian
          </label>
          <input
            v-model="localFilters.search"
            type="text"
            placeholder="Cari di catatan, akun, atau kategori..."
            class="w-full px-3 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm"
          />
        </div>

        <!-- Transaction Type -->
        <div>
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 block">
            Tipe Transaksi
          </label>
          <select
            v-model="localFilters.type"
            class="w-full px-3 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm"
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
          <div class="max-h-32 overflow-y-auto space-y-1 border border-gray-200 dark:border-gray-700 rounded-xl p-3">
            <label
              v-for="account in masterData.accounts"
              :key="account.id"
              class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors duration-200"
            >
                <input
                  type="checkbox"
                  :checked="(localFilters.account_ids || []).includes(account.id)"
                  @change="toggleAccount(account.id)"
                  class="text-teal-600 border-gray-300 rounded focus:ring-teal-500 flex-shrink-0"
                />
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ account.name }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                  {{ account.formatted_balance }}
                  <span v-if="account.category" class="ml-1">• {{ account.category.name }}</span>
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
          <div class="max-h-32 overflow-y-auto space-y-1 border border-gray-200 dark:border-gray-700 rounded-xl p-3">
            <label
              v-for="category in availableCategories"
              :key="category.id"
              class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors duration-200"
            >
                <input
                  type="checkbox"
                  :checked="(localFilters.category_ids || []).includes(category.id)"
                  @change="toggleCategory(category.id)"
                  class="text-teal-600 border-gray-300 rounded focus:ring-teal-500 flex-shrink-0"
                />
              <div class="flex-1">
                <div class="text-sm text-gray-900 dark:text-white">
                  {{ category.name }}
                  <span 
                    class="ml-2 text-xs px-2 py-0.5 rounded-full"
                    :class="category.type === 'income' 
                      ? 'bg-teal-100 text-teal-700 dark:bg-teal-900 dark:text-teal-300'
                      : 'bg-coral-100 text-coral-700 dark:bg-coral-900 dark:text-coral-300'"
                  >
                    {{ category.type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                  </span>
                </div>
              </div>
            </label>
          </div>
        </div>

        <!-- Tags -->
        <div>
          <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            <TagIcon class="w-4 h-4" />
            Tag
          </label>
          <div class="max-h-24 overflow-y-auto space-y-1 border border-gray-200 dark:border-gray-700 rounded-xl p-3">
            <label
              v-for="tag in masterData.tags"
              :key="tag.id"
              class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors duration-200"
            >
                <input
                  type="checkbox"
                  :checked="(localFilters.tag_ids || []).includes(tag.id)"
                  @change="toggleTag(tag.id)"
                  class="text-teal-600 border-gray-300 rounded focus:ring-teal-500 flex-shrink-0"
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
          <div class="space-y-3">
            <div>
              <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">Minimal</label>
              <input
                v-model.number="localFilters.amount_min"
                type="number"
                min="0"
                step="1000"
                placeholder="0"
                class="w-full px-3 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm"
              />
            </div>
            <div>
              <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">Maksimal</label>
              <input
                v-model.number="localFilters.amount_max"
                type="number"
                min="0"
                step="1000"
                placeholder="Tidak terbatas"
                class="w-full px-3 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm"
              />
            </div>
          </div>
        </div>

        <!-- Transaction Flags -->
        <div>
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 block">
            Status Transaksi
          </label>
          <div class="grid grid-cols-1 gap-2">
            <label
              v-for="flag in masterData.flag_options"
              :key="flag.value"
              class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors duration-200"
            >
                <input
                  type="checkbox"
                  :checked="(localFilters.flags || []).includes(flag.value)"
                  @change="toggleFlag(flag.value)"
                  class="text-teal-600 border-gray-300 rounded focus:ring-teal-500 flex-shrink-0"
                />
              <span class="text-sm text-gray-900 dark:text-white">{{ flag.label }}</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="flex gap-3 p-4 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
        <button
          @click="resetFilters"
          class="flex-1 py-3 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 font-medium text-sm"
        >
          Reset
        </button>
        
        <button
          @click="closeModal"
          class="flex-1 py-3 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 font-medium text-sm"
        >
          Batal
        </button>
        
        <button
          @click="applyFilters"
          class="flex-1 py-3 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-xl hover:from-teal-600 hover:to-teal-700 transition-all duration-200 font-medium text-sm"
        >
          Terapkan
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom scrollbar for overflow areas */
.max-h-32::-webkit-scrollbar,
.max-h-24::-webkit-scrollbar {
  width: 4px;
}

.max-h-32::-webkit-scrollbar-track,
.max-h-24::-webkit-scrollbar-track {
  background: transparent;
}

.max-h-32::-webkit-scrollbar-thumb,
.max-h-24::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 2px;
}

.max-h-32::-webkit-scrollbar-thumb:hover,
.max-h-24::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.8);
}

/* Ensure modal appears above everything */
.z-50 {
  z-index: 50;
}
</style>
