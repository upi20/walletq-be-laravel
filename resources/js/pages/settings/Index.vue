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
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Settings',
    href: '/settings',
  },
];

const settingsItems = [
  {
    title: 'Accounts',
    description: 'Manage your financial accounts',
    icon: CreditCard,
    href: '/settings/accounts',
    iconBg: 'bg-teal-100 dark:bg-teal-900',
    iconColor: 'text-teal-600 dark:text-teal-400'
  },
  {
    title: 'Account Categories',
    description: 'Organize your accounts by categories',
    icon: FolderOpen,
    href: '/settings/account-categories',
    iconBg: 'bg-coral-100 dark:bg-coral-900',
    iconColor: 'text-coral-600 dark:text-coral-400'
  },
  {
    title: 'Transaction Categories',
    description: 'Manage income and expense categories',
    icon: ArrowRightLeft,
    href: '/settings/transaction-categories',
    iconBg: 'bg-gradient-to-r from-teal-100 to-coral-100 dark:from-teal-900 dark:to-coral-900',
    iconColor: 'text-teal-600 dark:text-teal-400'
  },
  {
    title: 'Tags',
    description: 'Create and manage transaction tags',
    icon: Tag,
    href: '/settings/tags',
    iconBg: 'bg-gradient-to-r from-coral-100 to-teal-100 dark:from-coral-900 dark:to-teal-900',
    iconColor: 'text-coral-600 dark:text-coral-400'
  },
  {
    title: 'About',
    description: 'App information and system details',
    icon: Info,
    href: '/settings/about',
    iconBg: 'bg-gray-100 dark:bg-gray-800',
    iconColor: 'text-gray-600 dark:text-gray-400'
  },
];
</script>

<template>
  <FinancialAppLayout>
    <Head title="Settings" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">
        Settings
      </h1>
      <p class="text-gray-600 dark:text-gray-400">
        Manage your financial data and preferences
      </p>
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
    <div class="mt-12 grid grid-cols-2 gap-4">
      <div class="bg-gradient-to-r from-teal-500 to-teal-600 dark:from-teal-600 dark:to-teal-700 rounded-2xl p-4 text-white">
        <div class="text-2xl font-bold mb-1">5</div>
        <div class="text-sm opacity-90">Categories</div>
      </div>
      <div class="bg-gradient-to-r from-coral-500 to-coral-600 dark:from-coral-600 dark:to-coral-700 rounded-2xl p-4 text-white">
        <div class="text-2xl font-bold mb-1">∞</div>
        <div class="text-sm opacity-90">Customizable</div>
      </div>
    </div>
  </FinancialAppLayout>
</template>