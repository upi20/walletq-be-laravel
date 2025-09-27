<script setup lang="ts">
import { computed } from 'vue';
import { TrendingUp, TrendingDown, DollarSign } from 'lucide-vue-next';
import formatCurrency from '@/composables/formatCurrency';

interface Props {
  summary: {
    total_income: number;
    total_expense: number;
    net_amount: number;
    transaction_count: number;
    period_label: string;
  };
  quickStats: {
    today: { income: number; expense: number; };
    this_week: { income: number; expense: number; };
    this_month: { income: number; expense: number; };
  };
}

const props = defineProps<Props>();

// Format currency helper using composable
const formatCurrencyWithSign = (amount: number): string => {
  return formatCurrency(amount, 'currency', 'id-ID', 'IDR').replace('Rp', 'Rp ');
};

// Format number helper
const formatNumber = (num: number): string => {
  return new Intl.NumberFormat('id-ID').format(num);
};

// Computed values
const cards = computed(() => [
  {
    title: 'Total Pemasukan',
    amount: props.summary.total_income,
    icon: TrendingUp,
    color: 'text-teal-600 dark:text-teal-400',
    bgColor: 'bg-white/20 dark:bg-white/10',
    borderColor: 'border-teal-200/50 dark:border-teal-400/20',
  },
  {
    title: 'Total Pengeluaran',
    amount: props.summary.total_expense,
    icon: TrendingDown,
    color: 'text-coral-600 dark:text-coral-400',
    bgColor: 'bg-white/20 dark:bg-white/10',
    borderColor: 'border-coral-200/50 dark:border-coral-400/20',
  },
  {
    title: 'Saldo Bersih',
    amount: props.summary.net_amount,
    icon: DollarSign,
    color: props.summary.net_amount >= 0 
      ? 'text-teal-600 dark:text-teal-400' 
      : 'text-coral-600 dark:text-coral-400',
    bgColor: 'bg-white/20 dark:bg-white/10',
    borderColor: props.summary.net_amount >= 0
      ? 'border-teal-200/50 dark:border-teal-400/20'
      : 'border-coral-200/50 dark:border-coral-400/20',
  },
]);

const quickStatsData = computed(() => [
  {
    label: 'Hari Ini',
    income: props.quickStats.today.income,
    expense: props.quickStats.today.expense,
  },
  {
    label: 'Minggu Ini',
    income: props.quickStats.this_week.income,
    expense: props.quickStats.this_week.expense,
  },
  {
    label: 'Bulan Ini',
    income: props.quickStats.this_month.income,
    expense: props.quickStats.this_month.expense,
  },
]);
</script>

<template>
  <div class="space-y-4">
    <!-- Main Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div 
        v-for="(card, index) in cards" 
        :key="index"
        class="p-4 rounded-xl border backdrop-blur-sm transition-all duration-200 hover:scale-105"
        :class="[card.bgColor, card.borderColor]"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-white/80 text-sm font-medium">{{ card.title }}</p>
            <p class="text-white text-xl font-bold mt-1">
              {{ formatCurrencyWithSign(card.amount) }}
            </p>
          </div>
          <div class="p-2 bg-white/20 rounded-lg">
            <component :is="card.icon" class="w-6 h-6 text-white" />
          </div>
        </div>
      </div>
    </div>

    <!-- Transaction Count -->
    <div class="p-4 bg-white/20 dark:bg-white/10 rounded-xl border border-white/20 backdrop-blur-sm">
      <div class="text-center">
        <p class="text-white/80 text-sm font-medium">Total Transaksi</p>
        <p class="text-white text-2xl font-bold">
          {{ formatNumber(summary.transaction_count) }}
        </p>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-3 gap-3">
      <div 
        v-for="(stat, index) in quickStatsData" 
        :key="index"
        class="p-3 bg-white/10 dark:bg-white/5 rounded-lg border border-white/20 backdrop-blur-sm"
      >
        <p class="text-white/80 text-xs font-medium mb-2">{{ stat.label }}</p>
        <div class="space-y-1">
          <div class="flex items-center justify-between">
            <span class="text-white/70 text-xs">Masuk</span>
            <span class="text-teal-200 text-xs font-semibold">
              {{ stat.income > 0 ? formatCurrencyWithSign(stat.income).replace('Rp ', '+') : '-' }}
            </span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-white/70 text-xs">Keluar</span>
            <span class="text-coral-200 text-xs font-semibold">
              {{ stat.expense > 0 ? formatCurrencyWithSign(stat.expense).replace('Rp ', '-') : '-' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom styles for cards with glass morphism effect */
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
}
</style>
