<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onBeforeUnmount } from 'vue';
import { 
  Folder, 
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
import { useTranslation } from '@/composables/useTranslation';
import { useToast } from '@/composables/useToast';
import { useConfirmation } from '@/composables/useConfirmation';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import { AccountCategory } from '@/types';

interface Props {
  categories: {
    data: AccountCategory[];
    current_page: number;
    last_page: number;
  };
  filters: {
    search?: string;
  };
}

const props = defineProps<Props>();

// Translation
const { trans } = useTranslation();

// Toast notifications
const { success, error, warning, info } = useToast();

// Confirmation dialogs
const { confirmDelete, confirmAction, alert } = useConfirmation();

const activeMenu = ref<number | null>(null);

const toggleMenu = (categoryId: number) => {
  activeMenu.value = activeMenu.value === categoryId ? null : categoryId;
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
const sortField = ref<'name'>('name');
const sortOrder = ref<'asc' | 'desc'>('asc');

const filteredAndSortedCategories = computed(() => {
  // Server returns filtered & sorted list. Return a shallow copy for reactivity.
  return Array.isArray(props.categories.data) ? props.categories.data.slice() : [];
});

// perform a backend request with search param (updates URL)
const search = () => {
  router.get('/settings/account-categories', { 
    search: searchQuery.value,
    sortField: sortField.value,
    sortOrder: sortOrder.value,
  }, { 
    preserveState: true,
    preserveScroll: true,
    only: ['categories'],
  });
};

const toggleSort = (field: 'name') => {
  if (sortField.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortOrder.value = 'asc';
  }

  // trigger server-side sorting/filtering
  search();
};

const deleteCategory = async (category: AccountCategory) => {
  const confirmed = await confirmDelete(category.name);
  
  if (confirmed) {
    router.delete(`/settings/account-categories/${category.id}`, {
      onStart: () => {
        // info('Menghapus kategori...', {
        //   message: 'Sedang memproses permintaan Anda.',
        //   duration: 2000
        // });
      },
      onSuccess: () => {
        success('Kategori berhasil dihapus!', {
          message: `Kategori "${category.name}" telah dihapus dari sistem.`,
          duration: 5000
        });
      },
      onError: () => {
        error('Gagal menghapus kategori', {
          message: 'Terjadi kesalahan saat menghapus kategori. Silakan coba lagi.',
          persistent: true
        });
      }
    });
  }
};

// hide all toggle menu when clicking outside
document.addEventListener('click', (event) => {
  const target = event.target as HTMLElement;
  if (!target.closest('.group')) {
    activeMenu.value = null;
  }
});

const selectedCategoriesIds = ref<number[]>([]);
const handleSelectCategory = (categoryId: number) => {
  if (selectedCategoriesIds.value.includes(categoryId)) {
    selectedCategoriesIds.value = selectedCategoriesIds.value.filter(id => id !== categoryId);
  } else {
    selectedCategoriesIds.value.push(categoryId);
  }
};

const idHasBeenSelected = (categoryId: number) => {
  return selectedCategoriesIds.value.includes(categoryId);
};

const getTypeLabel = (type?: string | null) => {
  const typeMap: Record<string, string> = {
    'cash': 'Tunai',
    'bank': 'Bank',
    'e-wallet': 'E-Wallet'
  };
  return type ? typeMap[type] || type : '-';
};

const getTypeBadgeClass = (type?: string | null) => {
  const classMap: Record<string, string> = {
    'cash': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    'bank': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    'e-wallet': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
  };
  return type ? classMap[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
};

</script>

<template>
  <FinancialAppLayout :showHeader="false" :showFab="false" containerClass="px-3 pt-6 pb-32">
    <Head title="Kategori Rekening" />

    <!-- Header -->
    <div class="mb-1">
      <div class="flex items-center justify-between mb-4">
        <Link
          href="/settings"
          class="inline-flex items-center text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300 transition-colors"
        >
          <ArrowLeft class="w-4 h-4 mr-2" />
          Kembali ke Pengaturan
        </Link>
        
        <div class="flex items-center gap-2">
          <LanguageSwitcher />
        </div>
      </div>
      
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-gradient-to-r from-teal-500 to-coral-500 rounded-xl flex items-center justify-center">
              <Folder class="w-5 h-5 text-white" />
            </div>
            <div>
              <h1 class="text-1xl font-bold bg-gradient-to-r from-teal-600 to-coral-600 bg-clip-text text-transparent">
                Kategori Rekening
              </h1>
              <h3 class="text-xs">
                Kelola kategori untuk mengorganisir rekening Anda
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
            placeholder="Cari kategori rekening..."
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
              Nama
              <ChevronUp v-if="sortField === 'name' && sortOrder === 'asc'" class="w-4 h-4" />
              <ChevronDown v-else-if="sortField === 'name' && sortOrder === 'desc'" class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Categories List -->
    <div v-if="categories.data.length === 0" class="text-center py-16">
      <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
        <Folder class="w-12 h-12 text-gray-400" />
      </div>
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Belum ada kategori rekening</h3>
      <p class="text-gray-600 dark:text-gray-400 mb-6">Mulai dengan menambahkan kategori rekening pertama Anda</p>
      <Link
        href="/settings/account-categories/create"
        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-teal-600 to-coral-600 text-white rounded-xl font-medium hover:from-teal-700 hover:to-coral-700 transition-all duration-200"
      >
        <Plus class="w-5 h-5 mr-2" />
        Tambah Kategori
      </Link>
    </div>

    <div v-else-if="filteredAndSortedCategories.length === 0" class="text-center py-12">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Tidak ada hasil</h3>
      <p class="text-gray-600 dark:text-gray-400">Coba ubah kata kunci pencarian Anda</p>
    </div>

    <div v-else class="grid grid-cols-1 gap-3 mb-20">
      <div
        v-for="(category, index) in filteredAndSortedCategories"
        :key="category.id"
        :class="[
          'group rounded-xl p-4 shadow-sm border transition-all duration-300',
          index % 2 === 0 
            ? 'bg-white dark:bg-gray-800 border-none hover:shadow-lg hover:shadow-teal-500/10' 
            : 'bg-teal-50 dark:bg-teal-900/20 border-none hover:shadow-lg hover:shadow-coral-500/10'
        ]"
      >
        <!-- Category Header -->
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3" @click="handleSelectCategory(category.id)">
            <div class="w-8 h-8 bg-gradient-to-r from-teal-100 to-coral-100 dark:from-teal-900 dark:to-coral-900 rounded-md flex items-center justify-center">
              <Folder v-if="idHasBeenSelected(category.id) == false" class="w-6 h-6 text-teal-600 dark:text-teal-400" />
              <Check v-else class="w-6 h-6 text-teal-600 dark:text-teal-400" />
            </div>
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">
                {{ category.name }}
              </h3>
              <div class="flex items-center gap-2 mb-1">
                <span 
                  :class="['text-xs px-2 py-1 rounded-full', getTypeBadgeClass(category.type)]"
                >
                  {{ getTypeLabel(category.type) }}
                </span>
                <span v-if="category.is_default" class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                  Default
                </span>
              </div>
            </div>
          </div>
          
          <div class="relative">
            <button
              class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center transition-colors"
              @click="toggleMenu(category.id)"
            >
              <MoreVertical class="w-4 h-4 text-gray-400" />
            </button>
            
            <!-- Dropdown Menu -->
            <div
              v-if="activeMenu === category.id"
              class="absolute right-0 top-full mt-1 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-10"
            >
              <Link
                :href="`/settings/account-categories/${category.id}/edit`"
                class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                <Edit class="w-4 h-4 mr-3" />
                Edit Kategori
              </Link>
              <button
                @click="deleteCategory(category)"
                class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                :disabled="category.is_default"
              >
                <Trash2 class="w-4 h-4 mr-3" />
                Hapus Kategori
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="fixed bottom-20 left-1/2 transform -translate-x-1/2 w-full max-w-[640px] px-4 pointer-events-none z-40">
      <!-- between -->
      <div class="flex pointer-events-auto justify-between">
        <button @click="toggleFilterModal"
          class="w-14 h-14 bg-gradient-to-r from-teal-500 to-teal-600 dark:from-teal-600 dark:to-teal-700 rounded-full shadow-xl flex items-center justify-center transition-all duration-300 hover:scale-105 active:scale-95">
          <Filter class="w-6 h-6 text-white" />
        </button>
        <Link href="/settings/account-categories/create"
          class="w-14 h-14 bg-gradient-to-r from-teal-500 to-teal-600 dark:from-teal-600 dark:to-teal-700 rounded-full shadow-xl flex items-center justify-center transition-all duration-300 hover:scale-105 active:scale-95">
        <Plus class="w-6 h-6 text-white" />
        </Link>
      </div>
    </div>
  </FinancialAppLayout>
</template>