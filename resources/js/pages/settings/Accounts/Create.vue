<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
  CreditCard, 
  ArrowLeft,
  Save,
  DollarSign
} from 'lucide-vue-next';

import FinancialAppLayout from '@/layouts/FinancialAppLayout.vue';

interface AccountCategory {
  id: number;
  name: string;
}

interface Props {
  categories: AccountCategory[];
}

const props = defineProps<Props>();

const form = useForm({
  name: '',
  description: '',
  account_category_id: '',
  initial_balance: 0,
  is_active: true,
});

const submit = () => {
  form.post('/settings/accounts');
};

const formatNumber = (value: string) => {
  const number = parseFloat(value.replace(/[^0-9.-]/g, ''));
  return isNaN(number) ? 0 : number;
};
</script>

<template>
  <FinancialAppLayout>
    <Head title="Create Account" />

    <!-- Header -->
    <div class="mb-8">
      <Link
        href="/settings/accounts"
        class="inline-flex items-center text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300 transition-colors mb-4"
      >
        <ArrowLeft class="w-4 h-4 mr-2" />
        Back to Accounts
      </Link>
      
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 bg-gradient-to-r from-teal-500 to-coral-500 rounded-xl flex items-center justify-center">
          <CreditCard class="w-5 h-5 text-white" />
        </div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-teal-600 to-coral-600 bg-clip-text text-transparent">
          Create Account
        </h1>
      </div>
      <p class="text-gray-600 dark:text-gray-300">
        Add a new financial account to track your money
      </p>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Account Name -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Account Name *
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            placeholder="e.g., Main Checking Account"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            :class="{ 'border-red-500': form.errors.name }"
          />
          <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.name }}
          </p>
        </div>

        <!-- Category -->
        <div>
          <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Category *
          </label>
          <select
            id="category"
            v-model="form.account_category_id"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            :class="{ 'border-red-500': form.errors.account_category_id }"
          >
            <option value="">Select a category</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
          <p v-if="form.errors.account_category_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.account_category_id }}
          </p>
        </div>

        <!-- Initial Balance -->
        <div>
          <label for="balance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Initial Balance
          </label>
          <div class="relative">
            <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
              <DollarSign class="w-5 h-5 text-gray-400" />
            </div>
            <input
              id="balance"
              v-model="form.initial_balance"
              type="number"
              step="0.01"
              placeholder="0.00"
              class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-teal-500 focus:border-transparent"
              :class="{ 'border-red-500': form.errors.initial_balance }"
            />
          </div>
          <p v-if="form.errors.initial_balance" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.initial_balance }}
          </p>
        </div>

        <!-- Description -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Description
          </label>
          <textarea
            id="description"
            v-model="form.description"
            rows="3"
            placeholder="Optional description for this account"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-teal-500 focus:border-transparent resize-none"
            :class="{ 'border-red-500': form.errors.description }"
          />
          <p v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.description }}
          </p>
        </div>

        <!-- Active Status -->
        <div class="flex items-center gap-3">
          <input
            id="is_active"
            v-model="form.is_active"
            type="checkbox"
            class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500 dark:focus:ring-teal-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
          />
          <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Active account
          </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
          <div class="flex gap-4">
            <button
              type="submit"
              :disabled="form.processing"
              class="flex-1 flex items-center justify-center px-6 py-3 bg-gradient-to-r from-teal-600 to-coral-600 text-white rounded-xl font-medium hover:from-teal-700 hover:to-coral-700 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <Save class="w-5 h-5 mr-2" />
              {{ form.processing ? 'Creating...' : 'Create Account' }}
            </button>
            
            <Link
              href="/settings/accounts"
              class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              Cancel
            </Link>
          </div>
        </div>
      </form>
    </div>
  </FinancialAppLayout>
</template>