<script setup lang="ts">
import FinancialAppLayout from '@/layouts/FinancialAppLayout.vue';
import TransactionOverview from '@/components/TransactionOverview.vue';
import { Head } from '@inertiajs/vue3';
import { 
  Wallet, 
  TrendingUp, 
  TrendingDown, 
  PieChart 
} from 'lucide-vue-next';
import formatCurrency from '@/composables/formatCurrency';

// Mock data - nanti akan diganti dengan data dari API
const quickStats = [
  {
    label: 'Income This Month',
    amount: 5500000,
    type: 'income' as const,
    icon: TrendingUp,
    change: '+12%',
    changeType: 'positive' as const,
  },
  {
    label: 'Expenses This Month',
    amount: 2050000,
    type: 'expense' as const,
    icon: TrendingDown,
    change: '-8%',
    changeType: 'positive' as const,
  },
];

const recentTransactions = [
  {
    id: 1,
    title: 'Grocery Shopping',
    category: 'food',
    amount: -125000,
    type: 'expense' as const,
    date: '2025-09-23',
  },
  {
    id: 2,
    title: 'Salary Payment',
    category: 'salary',
    amount: 5000000,
    type: 'income' as const,
    date: '2025-09-22',
  },
  {
    id: 3,
    title: 'Uber Ride',
    category: 'transport',
    amount: -35000,
    type: 'expense' as const,
    date: '2025-09-22',
  },
  {
    id: 4,
    title: 'Coffee Shop',
    category: 'food',
    amount: -45000,
    type: 'expense' as const,
    date: '2025-09-21',
  },
  {
    id: 5,
    title: 'Freelance Work',
    category: 'salary',
    amount: 1500000,
    type: 'income' as const,
    date: '2025-09-20',
  },
];

const props = defineProps<{
  user: {
    name: string;
    balance: number;
  };
}>();

</script>

<template>
    <Head title="Dashboard" />

    <FinancialAppLayout>
        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div 
                v-for="stat in quickStats" 
                :key="stat.label"
                class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm transition-colors duration-300"
            >
                <div class="flex items-center justify-between mb-3">
                    <div 
                        class="w-8 h-8 rounded-lg flex items-center justify-center"
                        :class="stat.type === 'income' ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'"
                    >
                        <component :is="stat.icon" class="w-4 h-4" />
                    </div>
                    <span 
                        class="text-xs font-medium px-2 py-1 rounded-full"
                        :class="stat.changeType === 'positive' ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'"
                    >
                        {{ stat.change }}
                    </span>
                </div>
                
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-1">{{ stat.label }}</p>
                <p 
                    class="text-lg font-bold"
                    :class="stat.type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                >
                    {{ formatCurrency(props.user.balance) }}
                </p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm mb-6 transition-colors duration-300">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-3 gap-3">
                <button class="flex flex-col items-center p-4 bg-teal-50 dark:bg-teal-900/30 rounded-xl hover:bg-teal-100 dark:hover:bg-teal-800/40 transition-colors duration-200">
                    <div class="w-10 h-10 bg-teal-500 dark:bg-teal-600 rounded-full flex items-center justify-center mb-2">
                        <TrendingUp class="w-5 h-5 text-white" />
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Add Income</span>
                </button>
                
                <button class="flex flex-col items-center p-4 bg-red-50 dark:bg-red-900/30 rounded-xl hover:bg-red-100 dark:hover:bg-red-800/40 transition-colors duration-200">
                    <div class="w-10 h-10 bg-red-500 dark:bg-red-600 rounded-full flex items-center justify-center mb-2">
                        <TrendingDown class="w-5 h-5 text-white" />
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Add Expense</span>
                </button>
                
                <button class="flex flex-col items-center p-4 bg-blue-50 dark:bg-blue-900/30 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-800/40 transition-colors duration-200">
                    <div class="w-10 h-10 bg-blue-500 dark:bg-blue-600 rounded-full flex items-center justify-center mb-2">
                        <Wallet class="w-5 h-5 text-white" />
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Transfer</span>
                </button>
            </div>
        </div>

        <!-- Transaction Overview Component -->
        <TransactionOverview :transactions="recentTransactions" class="pb-32" />
    </FinancialAppLayout>
</template>
