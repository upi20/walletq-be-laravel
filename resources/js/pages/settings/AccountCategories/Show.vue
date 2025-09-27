<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
  Folder, 
  ArrowLeft,
  Edit,
  Calendar,
  Hash
} from 'lucide-vue-next';
import FinancialAppLayout from '@/layouts/FinancialAppLayout.vue';
import { useTranslation } from '@/composables/useTranslation';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import { AccountCategory } from '@/types';

// Translation
const { trans } = useTranslation();

interface Props {
  category: AccountCategory;
}

const props = defineProps<Props>();

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

const formatDate = (dateString?: string) => {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

</script>

<template>
  <FinancialAppLayout :showHeader="false" :showFab="false" containerClass="px-3 pt-6 pb-32">
    <Head :title="`Detail Kategori: ${category.name}`" />

    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <Link
          href="/settings/account-categories"
          class="inline-flex items-center text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300 transition-colors mb-4"
        >
          <ArrowLeft class="w-4 h-4 mr-2" />
          Kembali ke Daftar Kategori
        </Link>
        <LanguageSwitcher />
      </div>
      
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 bg-gradient-to-r from-teal-500 to-coral-500 rounded-xl flex items-center justify-center">
          <Folder class="w-5 h-5 text-white" />
        </div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-teal-600 to-coral-600 bg-clip-text text-transparent">
          {{ category.name }}
        </h1>
      </div>
      <p class="text-gray-600 dark:text-gray-300">
        Detail informasi kategori rekening
      </p>
    </div>

    <!-- Category Details -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- ID -->
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mt-1">
            <Hash class="w-4 h-4 text-gray-600 dark:text-gray-400" />
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">ID</h3>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ category.id }}</p>
          </div>
        </div>

        <!-- Name -->
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-teal-100 dark:bg-teal-900 rounded-lg flex items-center justify-center mt-1">
            <Folder class="w-4 h-4 text-teal-600 dark:text-teal-400" />
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Kategori</h3>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ category.name }}</p>
          </div>
        </div>

        <!-- Type -->
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mt-1">
            <Folder class="w-4 h-4 text-blue-600 dark:text-blue-400" />
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Jenis</h3>
            <span 
              v-if="category.type"
              :class="['inline-block px-3 py-1 rounded-full text-sm font-medium', getTypeBadgeClass(category.type)]"
            >
              {{ getTypeLabel(category.type) }}
            </span>
            <p v-else class="text-lg text-gray-500 dark:text-gray-400">-</p>
          </div>
        </div>

        <!-- Status -->
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center mt-1">
            <Folder class="w-4 h-4 text-yellow-600 dark:text-yellow-400" />
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h3>
            <div class="flex items-center gap-2">
              <span 
                v-if="category.is_default"
                class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200"
              >
                Default
              </span>
              <span 
                class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
              >
                Aktif
              </span>
            </div>
          </div>
        </div>

        <!-- Created At -->
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mt-1">
            <Calendar class="w-4 h-4 text-gray-600 dark:text-gray-400" />
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Dibuat Pada</h3>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatDate(category.created_at) }}</p>
          </div>
        </div>

        <!-- Updated At -->
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mt-1">
            <Calendar class="w-4 h-4 text-gray-600 dark:text-gray-400" />
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Terakhir Diperbarui</h3>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatDate(category.updated_at) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-4">
      <Link
        :href="`/settings/account-categories/${category.id}/edit`"
        class="flex-1 flex items-center justify-center px-6 py-3 bg-gradient-to-r from-teal-600 to-coral-600 text-white rounded-xl font-medium hover:from-teal-700 hover:to-coral-700 transition-all duration-200 shadow-lg hover:shadow-xl"
      >
        <Edit class="w-5 h-5 mr-2" />
        Edit Kategori
      </Link>
      
      <Link
        href="/settings/account-categories"
        class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
      >
        Kembali
      </Link>
    </div>
  </FinancialAppLayout>
</template>