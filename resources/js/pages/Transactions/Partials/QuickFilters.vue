<script setup lang="ts">
import { ref, computed } from 'vue';
import { Calendar, Clock, TrendingUp } from 'lucide-vue-next';

interface Props {
  currentPreset?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  applyPreset: [preset: string];
}>();

// Quick filter options
const quickFilters = ref([
  {
    value: 'today',
    label: 'Hari Ini',
    icon: Clock,
    color: 'teal'
  },
  {
    value: 'week',
    label: 'Minggu Ini',
    icon: Calendar,
    color: 'teal'
  },
  {
    value: 'month',
    label: 'Bulan Ini',
    icon: TrendingUp,
    color: 'teal'
  },
  {
    value: 'year',
    label: 'Tahun Ini',
    icon: Calendar,
    color: 'coral'
  }
]);

// Handle filter selection
const handleFilterClick = (filterValue: string) => {
  emit('applyPreset', filterValue);
};

// Check if filter is active
const isActive = (filterValue: string) => {
  return props.currentPreset === filterValue;
};

// Get button classes - Mobile Optimized
const getButtonClasses = (filter: any) => {
  const baseClasses = 'flex items-center gap-2 px-4 py-3 rounded-full text-sm font-medium transition-all duration-200 min-h-[44px]';
  
  if (isActive(filter.value)) {
    return filter.color === 'teal' 
      ? `${baseClasses} bg-gradient-to-r from-teal-500 to-teal-600 text-white shadow-lg`
      : `${baseClasses} bg-gradient-to-r from-coral-500 to-coral-600 text-white shadow-lg`;
  }
  
  return `${baseClasses} bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700`;
};

// Get icon classes
const getIconClasses = (filter: any) => {
  if (isActive(filter.value)) {
    return 'w-4 h-4 text-white';
  }
  
  return filter.color === 'teal' 
    ? 'w-4 h-4 text-teal-600 dark:text-teal-400'
    : 'w-4 h-4 text-coral-600 dark:text-coral-400';
};
</script>

<template>
  <!-- Mobile Optimized Quick Filters -->
  <div class="flex overflow-x-auto pb-2 -mx-4 px-4 gap-3 scrollbar-hide">
    <button
      v-for="filter in quickFilters"
      :key="filter.value"
      @click="handleFilterClick(filter.value)"
      :class="getButtonClasses(filter)"
      class="flex-shrink-0"
    >
      <component 
        :is="filter.icon" 
        :class="getIconClasses(filter)"
      />
      <span class="whitespace-nowrap">{{ filter.label }}</span>
    </button>
    
    <!-- Clear/All filter -->
    <button
      @click="handleFilterClick('all')"
      :class="isActive('all') || !currentPreset 
        ? 'flex items-center gap-2 px-4 py-3 rounded-full text-sm font-medium bg-gray-500 text-white shadow-lg transition-all duration-200 flex-shrink-0'
        : 'flex items-center gap-2 px-4 py-3 rounded-full text-sm font-medium bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 flex-shrink-0'"
    >
      <Calendar class="w-4 h-4" />
      <span class="whitespace-nowrap">Semua</span>
    </button>
  </div>
</template>

<style scoped>
/* Additional styles for smooth transitions and mobile scrolling */
button {
  user-select: none;
}

button:active {
  transform: scale(0.98);
}

/* Hide scrollbar for mobile */
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

/* Add momentum scrolling for iOS */
.scrollbar-hide {
  -webkit-overflow-scrolling: touch;
}
</style>
