<script setup lang="ts">
import { computed } from 'vue';
import { 
  ShoppingBag, 
  Car, 
  Utensils, 
  Home as HomeIcon, 
  Briefcase,
  Gift,
  TrendingUp,
  TrendingDown
} from 'lucide-vue-next';

interface Transaction {
  id: number;
  title: string;
  category: string;
  amount: number;
  type: 'income' | 'expense';
  date: string;
  icon?: string;
}

interface Props {
  transactions: Transaction[];
  showChart?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  showChart: true,
});

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount);
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
  });
};

const getCategoryIcon = (category: string, type: 'income' | 'expense') => {
  const iconMap: Record<string, any> = {
    'food': Utensils,
    'transport': Car,
    'shopping': ShoppingBag,
    'home': HomeIcon,
    'salary': Briefcase,
    'gift': Gift,
  };
  
  return iconMap[category.toLowerCase()] || (type === 'income' ? TrendingUp : TrendingDown);
};

const getCategoryColor = (category: string, type: 'income' | 'expense') => {
  if (type === 'income') {
    return 'bg-green-100 text-green-600';
  }
  
  const colorMap: Record<string, string> = {
    'food': 'bg-orange-100 text-orange-600',
    'transport': 'bg-blue-100 text-blue-600',
    'shopping': 'bg-purple-100 text-purple-600',
    'home': 'bg-indigo-100 text-indigo-600',
  };
  
  return colorMap[category.toLowerCase()] || 'bg-red-100 text-red-600';
};

// Mock chart data - nanti akan diganti dengan chart library
const chartData = computed(() => {
  const last7Days = Array.from({ length: 7 }, (_, i) => {
    const date = new Date();
    date.setDate(date.getDate() - (6 - i));
    return {
      date: date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }),
      amount: Math.random() * 1000000 + 500000,
    };
  });
  return last7Days;
});
</script>

<template>
  <div class="space-y-6">
    <!-- Chart Section -->
    <div v-if="showChart" class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm transition-colors duration-300">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Spending Overview</h3>
        <select class="text-sm text-gray-600 dark:text-gray-400 bg-transparent border-none focus:outline-none">
          <option>This Week</option>
          <option>This Month</option>
          <option>This Year</option>
        </select>
      </div>
      
      <!-- Simple Chart Placeholder -->
      <div class="h-30 bg-gradient-to-r from-teal-50 to-teal-100 dark:from-teal-900/30 dark:to-teal-800/30 rounded-xl flex items-end justify-between p-4 space-x-2 transition-colors duration-300">
        <div 
          v-for="(data, index) in chartData" 
          :key="index"
          class="flex flex-col items-center flex-1"
        >
          <div 
            class="w-full bg-gradient-to-t from-teal-500 to-teal-400 dark:from-teal-600 dark:to-teal-500 rounded-t-md transition-all duration-500"
            :style="{ height: `${(data.amount / 1500000) * 80}px` }"
          ></div>
          <span class="text-xs text-gray-600 dark:text-gray-400 mt-2">{{ data.date }}</span>
        </div>
      </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm transition-colors duration-300">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Recent Transactions</h3>
        <button class="text-sm text-teal-600 dark:text-teal-400 font-medium hover:text-teal-700 dark:hover:text-teal-300 transition-colors">
          See All
        </button>
      </div>

      <div class="space-y-3">
        <div 
          v-for="transaction in transactions" 
          :key="transaction.id"
          class="flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition-colors duration-200"
        >
          <!-- Icon & Info -->
          <div class="flex items-center space-x-3">
            <!-- Category Icon -->
            <div 
              class="w-10 h-10 rounded-full flex items-center justify-center relative"
              :class="getCategoryColor(transaction.category, transaction.type)"
            >
              <component 
                :is="getCategoryIcon(transaction.category, transaction.type)" 
                class="w-5 h-5" 
              />
              
              <!-- Type Badge -->
              <div 
                class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full flex items-center justify-center"
                :class="transaction.type === 'income' ? 'bg-green-500' : 'bg-red-500'"
              >
                <component 
                  :is="transaction.type === 'income' ? TrendingUp : TrendingDown" 
                  class="w-2.5 h-2.5 text-white" 
                />
              </div>
            </div>

            <!-- Transaction Details -->
            <div>
              <h4 class="font-medium text-gray-800 dark:text-gray-100 text-base">
                {{ transaction.title }}
              </h4>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ transaction.category }} • {{ formatDate(transaction.date) }}
              </p>
            </div>
          </div>

          <!-- Amount -->
          <div class="text-right">
            <p 
              class="font-semibold text-lg"
              :class="transaction.type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
            >
              {{ transaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(Math.abs(transaction.amount)) }}
            </p>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="transactions.length === 0" class="text-center py-8">
          <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 transition-colors duration-300">
            <TrendingUp class="w-8 h-8 text-gray-400 dark:text-gray-500" />
          </div>
          <h4 class="text-gray-600 dark:text-gray-300 font-medium mb-2">No transactions yet</h4>
          <p class="text-gray-500 dark:text-gray-400 text-sm">Start by adding your first transaction</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.h-30 {
  height: 7.5rem; /* 120px */
}
</style>