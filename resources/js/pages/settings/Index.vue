<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
  CreditCard, 
  FolderOpen, 
  ArrowRightLeft, 
  Tag, 
  Info,
  ChevronRight 
} from 'lucide-vue-next';
import FinancialAppLayout from '@/layouts/FinancialAppLayout.vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import { useTranslation } from '@/composables/useTranslation';
import { reactive, computed } from 'vue';

// Translation
const { trans } = useTranslation();

const settingsItems = computed(() => [
  {
    title: trans('settings.items.accounts.title'),
    description: trans('settings.items.accounts.description'),
    icon: CreditCard,
    href: '/settings/accounts',
    iconBg: 'bg-teal-100 dark:bg-teal-900',
    iconColor: 'text-teal-600 dark:text-teal-400'
  },
  {
    title: trans('settings.items.account_categories.title'),
    description: trans('settings.items.account_categories.description'),
    icon: FolderOpen,
    href: '/settings/account-categories',
    iconBg: 'bg-coral-100 dark:bg-coral-900',
    iconColor: 'text-coral-600 dark:text-coral-400'
  },
  {
    title: trans('settings.items.transaction_categories.title'),
    description: trans('settings.items.transaction_categories.description'),
    icon: ArrowRightLeft,
    href: '/settings/transaction-categories',
    iconBg: 'bg-gradient-to-r from-teal-100 to-coral-100 dark:from-teal-900 dark:to-coral-900',
    iconColor: 'text-teal-600 dark:text-teal-400'
  },
  {
    title: trans('settings.items.tags.title'),
    description: trans('settings.items.tags.description'),
    icon: Tag,
    href: '/settings/tags',
    iconBg: 'bg-gradient-to-r from-coral-100 to-teal-100 dark:from-coral-900 dark:to-teal-900',
    iconColor: 'text-coral-600 dark:text-coral-400'
  },
  {
    title: trans('settings.items.about.title'),
    description: trans('settings.items.about.description'),
    icon: Info,
    href: '/settings/about',
    iconBg: 'bg-gray-100 dark:bg-gray-800',
    iconColor: 'text-gray-600 dark:text-gray-400'
  },
]);

</script>

<template>
  <FinancialAppLayout :showHeader="false">
    <Head :title="trans('settings.index.title')" />

    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">
          {{ trans('settings.index.title') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
          {{ trans('settings.index.description') }}
        </p>
      </div>
      <LanguageSwitcher />
    </div>

    <!-- Settings Items -->
    <div class="space-y-4">
      <Link
        v-for="item in settingsItems"
        :key="item.href"
        :href="item.href"
        class="group block p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all duration-300"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <!-- Icon -->
            <div 
              :class="[
                'w-12 h-12 rounded-xl flex items-center justify-center transition-transform group-hover:scale-105',
                item.iconBg
              ]"
            >
              <component 
                :is="item.icon" 
                :class="['w-6 h-6', item.iconColor]" 
              />
            </div>

            <!-- Content -->
            <div>
              <h3 class="font-medium text-gray-900 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">
                {{ item.title }}
              </h3>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ item.description }}
              </p>
            </div>
          </div>

          <!-- Arrow -->
          <ChevronRight class="w-5 h-5 text-gray-400 group-hover:text-teal-500 transition-colors" />
        </div>
      </Link>
    </div>

    <!-- Stats Cards -->
    <!-- <div class="mt-12 grid grid-cols-2 gap-4">
      <div class="bg-gradient-to-r from-teal-500 to-teal-600 dark:from-teal-600 dark:to-teal-700 rounded-2xl p-4 text-white">
        <div class="text-2xl font-bold mb-1">5</div>
        <div class="text-sm opacity-90">Categories</div>
      </div>
      <div class="bg-gradient-to-r from-coral-500 to-coral-600 dark:from-coral-600 dark:to-coral-700 rounded-2xl p-4 text-white">
        <div class="text-2xl font-bold mb-1">∞</div>
        <div class="text-sm opacity-90">Customizable</div>
      </div>
    </div> -->
  </FinancialAppLayout>
</template>