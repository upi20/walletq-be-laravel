<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onBeforeUnmount } from 'vue';
import { 
  CreditCard, 
  Plus, 
  Search, 
  Edit, 
  Eye, 
  Trash2,
  MoreVertical,
  ArrowLeft,
  ChevronUp,
  ChevronDown,
  Filter,
  Check
} from 'lucide-vue-next';

import FinancialAppLayout from '@/layouts/FinancialAppLayout.vue';
import formatCurrency from '@/composables/formatCurrency';
import { useTranslation } from '@/composables/useTranslation';
import { useToast } from '@/composables/useToast';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';

interface Account {
  id: number;
  name: string;
  description: string | null;
  initial_balance: number;
  current_balance: number;
  is_active: boolean;
  category: {
    id: number;
    name: string;
  } | null;
  created_at: string;
}

interface AccountCategory {
  id: number;
  name: string;
}

interface Props {
  accounts: Account[];
  categories: AccountCategory[];
  filters: {
    search?: string;
  };
}

const props = defineProps<Props>();
const page = usePage();
const totalBalance = page.props.auth?.user.balance || 0;

// Translation
const { trans } = useTranslation();

// Toast notifications
const { success, error, warning, info } = useToast();

const activeMenu = ref<number | null>(null);

const toggleMenu = (accountId: number) => {
  activeMenu.value = activeMenu.value === accountId ? null : accountId;
};

const searchQuery = ref(props.filters.search || '');
const showFilterModal = ref(false);

const toggleFilterModal = () => {
  showFilterModal.value = !showFilterModal.value;
};

// debounce timer for search (keyup)
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const onKeyupSearch = (event: KeyboardEvent) => {
  // if the user pressed Enter, search immediately
  if (event.key === 'Enter') {
    if (searchTimeout) {
      clearTimeout(searchTimeout);
      searchTimeout = null;
    }
    search();
    return;
  }

  // otherwise debounce
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    search();
    searchTimeout = null;
  }, 500);
};

onBeforeUnmount(() => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
    searchTimeout = null;
  }
});

// client-side sorting & filtering (no backend request)
const sortField = ref<'name' | 'current_balance'>('name');
const sortOrder = ref<'asc' | 'desc'>('asc');

const filteredAndSortedAccounts = computed(() => {
  // Server returns filtered & sorted list. Return a shallow copy for reactivity.
  return Array.isArray(props.accounts) ? props.accounts.slice() : [];
});

// perform a backend request with search param (updates URL)
const search = () => {
  router.get('/settings/accounts', { 
    search: searchQuery.value,
    sortField: sortField.value,
    sortOrder: sortOrder.value,
  }, { 
    preserveState: true,
    preserveScroll: true,
    only: ['accounts'],
  });
};

const toggleSort = (field: 'name' | 'current_balance') => {
  if (sortField.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortOrder.value = 'asc';
  }

  // trigger server-side sorting/filtering
  search();
};

const deleteAccount = (account: Account) => {
  if (confirm(`${trans('accounts.delete_confirmation')} "${account.name}"?`)) {
    router.delete(`/settings/accounts/${account.id}`, {
      onStart: () => {
        info('Menghapus akun...', {
          message: 'Sedang memproses permintaan Anda.',
          duration: 2000
        });
      },
      onSuccess: () => {
        success('Akun berhasil dihapus!', {
          message: `Akun "${account.name}" telah dihapus dari sistem.`,
          duration: 5000
        });
      },
      onError: () => {
        error('Gagal menghapus akun', {
          message: 'Terjadi kesalahan saat menghapus akun. Silakan coba lagi.',
          persistent: true
        });
      }
    });
  }
};

// Example toast functions for demonstration
const showToastExamples = () => {
  // Success toast
  success('Operasi berhasil!', {
    message: 'Data telah disimpan dengan baik.',
    duration: 4000
  });
  
  // Error toast with persistent option
  setTimeout(() => {
    error('Terjadi kesalahan sistem', {
      message: 'Hubungi administrator jika masalah berlanjut.',
      persistent: true
    });
  }, 1000);
  
  // Warning toast
  setTimeout(() => {
    warning('Peringatan penting', {
      message: 'Pastikan data yang dimasukkan sudah benar.',
      duration: 6000
    });
  }, 2000);
  
  // Info toast
  setTimeout(() => {
    info('Informasi sistem', {
      message: 'Pemeliharaan sistem dijadwalkan pada hari Minggu.',
      duration: 5000
    });
  }, 3000);
};

// hide all toggle menu when clicking outside
document.addEventListener('click', (event) => {
  const target = event.target as HTMLElement;
  if (!target.closest('.group')) {
    activeMenu.value = null;
  }
});

const selectedAccountsIds = ref<number[]>([]);
const handleSelectAccount = (accountId: number) => {
  if (selectedAccountsIds.value.includes(accountId)) {
    selectedAccountsIds.value = selectedAccountsIds.value.filter(id => id !== accountId);
  } else {
    selectedAccountsIds.value.push(accountId);
  }
};

const idHasBeenSelected = (accountId: number) => {
  return selectedAccountsIds.value.includes(accountId);
};

const totalCurrentBalanceSelected = computed(() => {
  return props.accounts
    .filter(account => selectedAccountsIds.value.includes(account.id))
    .reduce((sum, account) => Number(sum) + Number(account.current_balance), 0); 
});

</script>

<template>
  <FinancialAppLayout :showHeader="false" :showFab="false">
    <Head :title="trans('accounts.title')" />

    <!-- Header -->
    <div class="mb-1">
      <div class="flex items-center justify-between mb-4">
        <Link
          href="/settings"
          class="inline-flex items-center text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300 transition-colors"
        >
          <ArrowLeft class="w-4 h-4 mr-2" />
          {{ trans('settings.back_to_settings') }}
        </Link>
        
        <div class="flex items-center gap-2">
          <LanguageSwitcher />
          <!-- Demo Toast Button -->
          <button
            @click="showToastExamples"
            class="px-3 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white text-sm rounded-lg hover:from-purple-600 hover:to-pink-600 transition-all duration-200 shadow-md hover:shadow-lg"
            title="Demo Toast Notifications"
          >
            🔔 Demo
          </button>
        </div>
      </div>
      
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-gradient-to-r from-teal-500 to-coral-500 rounded-xl flex items-center justify-center">
              <CreditCard class="w-5 h-5 text-white" />
            </div>
            <div>
              <h1 class="text-1xl font-bold bg-gradient-to-r from-teal-600 to-coral-600 bg-clip-text text-transparent">
                Total Saldo
              </h1>
              <h3 class="text-xs">
                {{ formatCurrency(totalBalance) }} 
                <template v-if="selectedAccountsIds.length > 0">
                  | Dipilih : {{ formatCurrency(totalCurrentBalanceSelected) }}
                </template>
              </h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Search -->
    <div 
      v-if="showFilterModal"
      class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 mb-6 mt-5">
      <div class="flex flex-col sm:flex-row gap-4">
        <form class="relative flex-1" @submit.prevent="search">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
          <input
            v-model="searchQuery"
            type="search"
            :placeholder="trans('accounts.search_placeholder')"
            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            @keyup="onKeyupSearch"
          />
        </form>
        <div class="flex items-center gap-2">
          <div class="flex items-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <button
              @click.prevent="toggleSort('name')"
              class="px-3 py-2 text-sm flex items-center gap-2"
            >
              {{ trans('common.name') }}
              <ChevronUp v-if="sortField === 'name' && sortOrder === 'asc'" class="w-4 h-4" />
              <ChevronDown v-else-if="sortField === 'name' && sortOrder === 'desc'" class="w-4 h-4" />
            </button>
            <button
              @click.prevent="toggleSort('current_balance')"
              class="px-3 py-2 text-sm flex items-center gap-2 border-l border-gray-200 dark:border-gray-700"
            >
              {{ trans('accounts.balance') }}
              <ChevronUp v-if="sortField === 'current_balance' && sortOrder === 'asc'" class="w-4 h-4" />
              <ChevronDown v-else-if="sortField === 'current_balance' && sortOrder === 'desc'" class="w-4 h-4" />
            </button>
          </div>
        </div>
        <!-- <button
          @click="search"
          class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors"
        >
          Search
        </button> -->
      </div>
    </div>

    <!-- Accounts List -->
    <div v-if="accounts.length === 0" class="text-center py-16">
      <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
        <CreditCard class="w-12 h-12 text-gray-400" />
      </div>
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ trans('accounts.no_accounts') }}</h3>
      <p class="text-gray-600 dark:text-gray-400 mb-6">{{ trans('accounts.no_accounts_subtitle') }}</p>
      <Link
        href="/settings/accounts/create"
        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-teal-600 to-coral-600 text-white rounded-xl font-medium hover:from-teal-700 hover:to-coral-700 transition-all duration-200"
      >
        <Plus class="w-5 h-5 mr-2" />
        {{ trans('accounts.create_account') }}
      </Link>
    </div>

    <div v-else-if="filteredAndSortedAccounts.length === 0" class="text-center py-12">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ trans('accounts.no_results') }}</h3>
      <p class="text-gray-600 dark:text-gray-400">{{ trans('accounts.no_results_subtitle') }}</p>
    </div>

    <div v-else class="grid grid-cols-1 gap-1 mb-20">
      <div
        v-for="(account, index) in filteredAndSortedAccounts"
        :key="account.id"
        :class="[
          'group rounded-md p-0 shadow-sm border transition-all duration-300',
          index % 2 === 0 
            ? 'bg-white dark:bg-gray-800 border-none hover:shadow-lg hover:shadow-teal-500/10' 
            : 'bg-teal-50 dark:bg-teal-900/20 border-none hover:shadow-lg hover:shadow-coral-500/10'
        ]"
      >
        <!-- Account Header -->
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3" @click="handleSelectAccount(account.id)">
            <div class="ms-2 w-8 h-8 bg-gradient-to-r from-teal-100 to-coral-100 dark:from-teal-900 dark:to-coral-900 rounded-md flex items-center justify-center">
              <CreditCard v-if="idHasBeenSelected(account.id) == false" class="w-6 h-6 text-teal-600 dark:text-teal-400" />
              <Check v-else class="w-6 h-6 text-teal-600 dark:text-teal-400" />
            </div>
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">
                {{ account.name }}
              </h3>
              <div class="flex items-center gap-2 mb-1">
                <span class="text-xs text-teal-700">
                  {{ formatCurrency(account.current_balance) }}
                </span>
              </div>
            </div>
          </div>
          
          <div class="relative">
            <button
              class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center transition-colors mt-2"
              @click="toggleMenu(account.id)"
            >
              <MoreVertical class="w-4 h-4 text-gray-400" />
            </button>
            
            <!-- Dropdown Menu -->
            <div
              v-if="activeMenu === account.id"
              class="absolute right-0 top-full mt-1 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-10"
            >
              <Link
                :href="`/settings/accounts/${account.id}/edit`"
                class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                <Edit class="w-4 h-4 mr-3" />
                {{ trans('accounts.edit_account') }}
              </Link>
              <button
                @click="deleteAccount(account)"
                class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
              >
                <Trash2 class="w-4 h-4 mr-3" />
                {{ trans('accounts.delete_account') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Floating Action Button Filter data -->
    <button
      @click="toggleFilterModal"
    class="fixed bottom-20 left-6 w-14 h-14 bg-gradient-to-r from-teal-500 to-teal-600 dark:from-teal-600 dark:to-teal-700 rounded-full shadow-xl flex items-center justify-center transition-all duration-300 hover:scale-105 active:scale-95"
    >
      <Filter class="w-6 h-6 text-white" />
    </button>

    <Link
      href="/settings/accounts/create"
      :title="trans('accounts.add_account')"
      class="fixed bottom-20 right-6 w-14 h-14 bg-gradient-to-r from-teal-500 to-teal-600 dark:from-teal-600 dark:to-teal-700 rounded-full shadow-xl flex items-center justify-center transition-all duration-300 hover:scale-105 active:scale-95"
    >
      <Plus class="w-6 h-6 text-white" />
    </Link>
  </FinancialAppLayout>
</template>