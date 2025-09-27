<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
  Info,
  ArrowLeft
} from 'lucide-vue-next';
import FinancialAppLayout from '@/layouts/FinancialAppLayout.vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import { useTranslation } from '@/composables/useTranslation';

// Translation
const { trans } = useTranslation();
interface Props {
  appInfo: {
    name: string;
    version: string;
    environment: string;
    debug: boolean;
    url: string;
    timezone: string;
    locale: string;
    currency: string;
    database_connection: string;
    cache_driver: string;
    session_driver: string;
    queue_driver: string;
    installed: boolean;
  };
}

const props = defineProps<Props>();

</script>

<template>
  <FinancialAppLayout :showHeader="false" :showFab="false">
    <Head :title="trans('settings.index.title')" />

    <!-- Header -->
    <div class="mb-3 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center transition-transform group-hover:scale-105 bg-gray-100 dark:bg-gray-800">
          <Info class="w-8 h-8 text-gray-400 dark:text-gray-500 mb-1" />
        </div>
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-0">
            Tentang
          </h1>
          <p class="text-gray-600 dark:text-gray-400">
            Informasi aplikasi dan detail sistem
          </p>
        </div>
      </div>
      <LanguageSwitcher />
    </div>
    <Link
      href="/settings"
      class="inline-flex mb-2 items-center text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300 transition-colors"
    >
      <ArrowLeft class="w-4 h-4 mr-2" />
      {{ trans('settings.back_to_settings') }}
    </Link>

    <!-- Settings Items -->
    <div class="block p-4 mt-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all duration-300">
      <h1 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
        {{ props.appInfo.name }} v{{ props.appInfo.version }}
      </h1>
      <h2 class="text-sm font-medium text-gray-600 dark:text-gray-400">
        By Isep Lutpi Nur <a href="https://github.com/upi20" class="text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300 transition-colors">github</a>
      </h2>
    </div>
    <!-- copyright -->
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">
      &copy; 2025 - {{ new Date().getFullYear() }} Isep Lutpi Nur. All rights reserved.
    </p>
  </FinancialAppLayout>
</template>