<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
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
  ChevronDown
} from 'lucide-vue-next';

import FinancialAppLayout from '@/layouts/FinancialAppLayout.vue';
import formatCurrency from '@/composables/formatCurrency';

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

const activeMenu = ref<number | null>(null);

const toggleMenu = (accountId: number) => {
  activeMenu.value = activeMenu.value === accountId ? null : accountId;
};

const searchQuery = ref(props.filters.search || '');

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
  if (confirm(`Are you sure you want to delete "${account.name}"?`)) {
    router.delete(`/settings/accounts/${account.id}`);
  }
};

// hide all toggle menu when clicking outside
document.addEventListener('click', (event) => {
  const target = event.target as HTMLElement;
  if (!target.closest('.group')) {
    activeMenu.value = null;
  }
});

</script>

<template>
  <FinancialAppLayout>
    <Head title="Accounts" />

    <!-- Header -->
    <div class="mb-8">
      <Link
        href="/settings"
        class="inline-flex items-center text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300 transition-colors mb-4"
      >
        <ArrowLeft class="w-4 h-4 mr-2" />
        Back to Settings
      </Link>
      
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-gradient-to-r from-teal-500 to-coral-500 rounded-xl flex items-center justify-center">
              <CreditCard class="w-5 h-5 text-white" />
            </div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-teal-600 to-coral-600 bg-clip-text text-transparent">
              Accounts
            </h1>
          </div>
          <p class="text-gray-600 dark:text-gray-300">
            Manage your financial accounts and track balances
          </p>
        </div>
        
        <Link
          href="/settings/accounts/create"
          class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-coral-600 text-white rounded-xl font-medium hover:from-teal-700 hover:to-coral-700 transition-all duration-200 shadow-lg hover:shadow-xl"
        >
          <Plus class="w-4 h-4 mr-2" />
          Add Account
        </Link>
      </div>
    </div>

    <!-- Search -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
      <div class="flex flex-col sm:flex-row gap-4">
        <form class="relative flex-1" @submit.prevent="search">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Search accounts..."
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
              Name
              <ChevronUp v-if="sortField === 'name' && sortOrder === 'asc'" class="w-4 h-4" />
              <ChevronDown v-else-if="sortField === 'name' && sortOrder === 'desc'" class="w-4 h-4" />
            </button>
            <button
              @click.prevent="toggleSort('current_balance')"
              class="px-3 py-2 text-sm flex items-center gap-2 border-l border-gray-200 dark:border-gray-700"
            >
              Balance
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
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No accounts yet</h3>
      <p class="text-gray-600 dark:text-gray-400 mb-6">Create your first account to get started</p>
      <Link
        href="/settings/accounts/create"
        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-teal-600 to-coral-600 text-white rounded-xl font-medium hover:from-teal-700 hover:to-coral-700 transition-all duration-200"
      >
        <Plus class="w-5 h-5 mr-2" />
        Create Account
      </Link>
    </div>

    <div v-else-if="filteredAndSortedAccounts.length === 0" class="text-center py-12">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No results</h3>
      <p class="text-gray-600 dark:text-gray-400">No accounts match your search or filter.</p>
    </div>

    <div v-else class="grid grid-cols-1 gap-4">
      <div
        v-for="(account, index) in filteredAndSortedAccounts"
        :key="account.id"
        :class="[
          'group rounded-xl p-6 shadow-sm border transition-all duration-300',
          index % 2 === 0 
            ? 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:shadow-lg hover:shadow-teal-500/10' 
            : 'bg-teal-50 dark:bg-teal-900/20 border-teal-200 dark:border-teal-700/50 hover:shadow-lg hover:shadow-coral-500/10'
        ]"
      >
        <!-- Account Header -->
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-r from-teal-100 to-coral-100 dark:from-teal-900 dark:to-coral-900 rounded-xl flex items-center justify-center">
              <CreditCard class="w-6 h-6 text-teal-600 dark:text-teal-400" />
            </div>
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">
                {{ account.name }}
              </h3>
              <div class="flex items-center gap-2 mt-1">
                <span class="px-2 py-1 text-xs font-medium bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300 rounded-lg">
                  <!-- {{ account.category?.name }} -->
                    {{ formatCurrency(account.current_balance) }}
                </span>
                <!-- <span 
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-lg',
                    account.is_active 
                      ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300'
                      : 'bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300'
                  ]"
                >
                  {{ account.is_active ? 'Active' : 'Inactive' }}
                </span> -->
              </div>
            </div>
          </div>
          
          <div class="relative">
            <button
              class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center transition-colors"
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
                :href="`/settings/accounts/${account.id}`"
                class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                <Eye class="w-4 h-4 mr-3" />
                View Details
              </Link>
              <Link
                :href="`/settings/accounts/${account.id}/edit`"
                class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                <Edit class="w-4 h-4 mr-3" />
                Edit Account
              </Link>
              <button
                @click="deleteAccount(account)"
                class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
              >
                <Trash2 class="w-4 h-4 mr-3" />
                Delete Account
              </button>
            </div>
          </div>
        </div>

        <!-- Account Details -->
        <!-- <div class="space-y-3">
          <div v-if="account.description" class="text-sm text-gray-600 dark:text-gray-400">
            {{ account.description }}
          </div>
          
          <div class="bg-gradient-to-r from-teal-50 to-coral-50 dark:from-teal-900/20 dark:to-coral-900/20 rounded-lg p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Initial Balance</div>
            <div class="text-2xl font-bold bg-gradient-to-r from-teal-600 to-coral-600 bg-clip-text text-transparent">
              {{ formatCurrency(account.initial_balance) }}
            </div>
          </div>
        </div> -->

        <!-- Account Actions -->
        <!-- <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
          <div class="flex gap-2">
            <Link
              :href="`/settings/accounts/${account.id}`"
              class="flex-1 px-3 py-2 text-sm font-medium text-teal-600 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-900/20 rounded-lg transition-colors text-center"
            >
              View
            </Link>
            <Link
              :href="`/settings/accounts/${account.id}/edit`"
              class="flex-1 px-3 py-2 text-sm font-medium text-coral-600 dark:text-coral-400 hover:bg-coral-50 dark:hover:bg-coral-900/20 rounded-lg transition-colors text-center"
            >
              Edit
            </Link>
          </div>
        </div> -->
      </div>
    </div>
  </FinancialAppLayout>
</template>