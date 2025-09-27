<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
  Hash, 
  ArrowLeft,
  Edit,
  Calendar
} from 'lucide-vue-next';
import FinancialAppLayout from '@/layouts/FinancialAppLayout.vue';
import { useTranslation } from '@/composables/useTranslation';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import { Tag } from '@/types';

// Translation
const { trans } = useTranslation();

interface Props {
  tag: Tag;
}

const props = defineProps<Props>();

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
    <Head :title="`Detail Tag: ${tag.name}`" />

    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <Link
          href="/settings/tags"
          class="inline-flex items-center text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300 transition-colors mb-4"
        >
          <ArrowLeft class="w-4 h-4 mr-2" />
          Kembali ke Daftar Tag
        </Link>
        <LanguageSwitcher />
      </div>
      
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 bg-gradient-to-r from-teal-500 to-coral-500 rounded-xl flex items-center justify-center">
          <Hash class="w-5 h-5 text-white" />
        </div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-teal-600 to-coral-600 bg-clip-text text-transparent">
          {{ tag.name }}
        </h1>
      </div>
      <p class="text-gray-600 dark:text-gray-300">
        Detail informasi tag
      </p>
    </div>

    <!-- Tag Details -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- ID -->
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mt-1">
            <Hash class="w-4 h-4 text-gray-600 dark:text-gray-400" />
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">ID</h3>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ tag.id }}</p>
          </div>
        </div>

        <!-- Name -->
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-teal-100 dark:bg-teal-900 rounded-lg flex items-center justify-center mt-1">
            <Hash class="w-4 h-4 text-teal-600 dark:text-teal-400" />
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Tag</h3>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ tag.name }}</p>
          </div>
        </div>

        <!-- Status -->
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mt-1">
            <Hash class="w-4 h-4 text-green-600 dark:text-green-400" />
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h3>
            <span class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
              Aktif
            </span>
          </div>
        </div>

        <!-- User -->
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mt-1">
            <Hash class="w-4 h-4 text-blue-600 dark:text-blue-400" />
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Pemilik</h3>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ tag.user?.name || 'Sistem' }}
            </p>
          </div>
        </div>

        <!-- Created At -->
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mt-1">
            <Calendar class="w-4 h-4 text-gray-600 dark:text-gray-400" />
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Dibuat Pada</h3>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatDate(tag.created_at) }}</p>
          </div>
        </div>

        <!-- Updated At -->
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mt-1">
            <Calendar class="w-4 h-4 text-gray-600 dark:text-gray-400" />
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Terakhir Diperbarui</h3>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatDate(tag.updated_at) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-4">
      <Link
        :href="`/settings/tags/${tag.id}/edit`"
        class="flex-1 flex items-center justify-center px-6 py-3 bg-gradient-to-r from-teal-600 to-coral-600 text-white rounded-xl font-medium hover:from-teal-700 hover:to-coral-700 transition-all duration-200 shadow-lg hover:shadow-xl"
      >
        <Edit class="w-5 h-5 mr-2" />
        Edit Tag
      </Link>
      
      <Link
        href="/settings/tags"
        class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
      >
        Kembali
      </Link>
    </div>
  </FinancialAppLayout>
</template>