<script setup lang="ts">
import { computed, watch, ref } from 'vue';
import { Pie } from 'vue-chartjs';
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend,
  ChartOptions,
  TooltipItem
} from 'chart.js';
import formatCurrency from '@/composables/formatCurrency';

// Register Chart.js components
ChartJS.register(ArcElement, Tooltip, Legend);

interface CategoryData {
  category_name: string;
  total_amount: number;
  percentage: number;
}

interface Props {
  data: CategoryData[];
  type: 'income' | 'expense';
  isDark?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  isDark: false
});

// Generate colors based on type
const generateColors = (count: number, type: 'income' | 'expense', isDark: boolean) => {
  if (type === 'income') {
    // Teal shades for income
    const lightColors = [
      'rgb(32, 178, 170)',   // teal-500
      'rgb(77, 208, 225)',   // teal-400
      'rgb(0, 131, 143)',    // teal-600
      'rgb(23, 162, 184)',   // teal variant
      'rgb(0, 96, 100)',     // teal-700
      'rgb(153, 246, 228)',  // teal-200
      'rgb(94, 234, 212)',   // teal-300
      'rgb(15, 118, 110)',   // teal-700
    ];

    const darkColors = [
      'rgb(77, 208, 225)',   // teal-400 (brighter for dark mode)
      'rgb(32, 178, 170)',   // teal-500
      'rgb(94, 234, 212)',   // teal-300
      'rgb(153, 246, 228)',  // teal-200
      'rgb(0, 131, 143)',    // teal-600
      'rgb(23, 162, 184)',   // teal variant
      'rgb(0, 96, 100)',     // teal-700
      'rgb(15, 118, 110)',   // teal-700
    ];

    return isDark ? darkColors.slice(0, count) : lightColors.slice(0, count);
  } else {
    // Coral shades for expense
    const lightColors = [
      'rgb(255, 107, 107)',  // coral-500
      'rgb(255, 138, 128)',  // coral-400
      'rgb(229, 57, 53)',    // coral-600
      'rgb(255, 82, 82)',    // coral variant
      'rgb(198, 40, 40)',    // coral-800
      'rgb(255, 200, 200)',  // coral-200
      'rgb(255, 176, 176)',  // coral-300
      'rgb(205, 40, 32)',    // coral-700
    ];

    const darkColors = [
      'rgb(255, 138, 128)',  // coral-400 (brighter for dark mode)
      'rgb(255, 107, 107)',  // coral-500
      'rgb(255, 176, 176)',  // coral-300
      'rgb(255, 200, 200)',  // coral-200
      'rgb(229, 57, 53)',    // coral-600
      'rgb(255, 82, 82)',    // coral variant
      'rgb(198, 40, 40)',    // coral-800
      'rgb(205, 40, 32)',    // coral-700
    ];

    return isDark ? darkColors.slice(0, count) : lightColors.slice(0, count);
  }
};

// Chart data
const chartData = computed(() => ({
  labels: props.data.map(item => item.category_name),
  datasets: [{
    data: props.data.map(item => item.total_amount),
    backgroundColor: generateColors(props.data.length, props.type, props.isDark),
    borderWidth: props.isDark ? 1 : 0,
    borderColor: props.isDark ? 'rgba(255, 255, 255, 0.1)' : 'transparent',
  }]
}));

// Chart options
const chartOptions = computed<ChartOptions<'pie'>>(() => ({
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        color: props.isDark ? '#FFFFFF' : '#424242',
        padding: 12,
        font: {
          size: 12,
          family: 'system-ui, -apple-system, sans-serif'
        },
        usePointStyle: true,
        pointStyle: 'circle',
      }
    },
    tooltip: {
      backgroundColor: props.isDark ? 'rgba(26, 26, 27, 0.95)' : 'rgba(255, 255, 255, 0.95)',
      titleColor: props.isDark ? '#FFFFFF' : '#424242',
      bodyColor: props.isDark ? '#B3B3B3' : '#757575',
      borderColor: props.isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
      borderWidth: 1,
      padding: 12,
      displayColors: true,
      callbacks: {
        label: function (context: TooltipItem<'pie'>) {
          const label = context.label || '';
          const value = formatCurrency(context.parsed as number, 'currency');
          const percentage = props.data[context.dataIndex].percentage;
          return `${label}: ${value} (${percentage}%)`;
        }
      }
    }
  }
}));

// Watch for dark mode changes and update chart
const chartKey = ref(0);
watch(() => props.isDark, () => {
  chartKey.value++;
});
</script>

<template>
  <div class="w-full h-full flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
      <Pie
        :key="chartKey"
        :data="chartData"
        :options="chartOptions" />
    </div>
  </div>
</template>
