<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { 
  CheckCircle, 
  XCircle, 
  AlertTriangle, 
  Info, 
  X 
} from 'lucide-vue-next';
import type { Toast } from '@/types/toast';

interface Props {
  toast: Toast;
}

interface Emits {
  (e: 'remove', id: string): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const isVisible = ref(false);

const iconComponent = computed(() => {
  switch (props.toast.type) {
    case 'success': return CheckCircle;
    case 'error': return XCircle;  
    case 'warning': return AlertTriangle;
    case 'info': return Info;
    default: return Info;
  }
});

const toastClasses = computed(() => {
  const baseClasses = 'flex items-start gap-3 p-4 rounded-lg shadow-lg border backdrop-blur-sm transition-all duration-300 transform';
  
  switch (props.toast.type) {
    case 'success':
      return `${baseClasses} bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200`;
    case 'error':
      return `${baseClasses} bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-800 dark:text-red-200`;
    case 'warning':
      return `${baseClasses} bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200`;
    case 'info':
      return `${baseClasses} bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200`;
    default:
      return `${baseClasses} bg-gray-50 dark:bg-gray-900/20 border-gray-200 dark:border-gray-800 text-gray-800 dark:text-gray-200`;
  }
});

const iconClasses = computed(() => {
  switch (props.toast.type) {
    case 'success': return 'text-green-500 dark:text-green-400';
    case 'error': return 'text-red-500 dark:text-red-400';
    case 'warning': return 'text-yellow-500 dark:text-yellow-400';
    case 'info': return 'text-blue-500 dark:text-blue-400';
    default: return 'text-gray-500 dark:text-gray-400';
  }
});

const handleRemove = () => {
  isVisible.value = false;
  setTimeout(() => {
    emit('remove', props.toast.id);
  }, 300);
};

onMounted(() => {
  setTimeout(() => {
    isVisible.value = true;
  }, 50);
});
</script>

<template>
  <div 
    :class="[
      toastClasses,
      isVisible ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-full'
    ]"
  >
    <!-- Icon -->
    <component 
      :is="iconComponent" 
      :class="['w-5 h-5 mt-0.5 flex-shrink-0', iconClasses]" 
    />

    <!-- Content -->
    <div class="flex-1 min-w-0">
      <h4 class="text-sm font-medium">
        {{ toast.title }}
      </h4>
      <p 
        v-if="toast.message" 
        class="text-sm opacity-90 mt-1"
      >
        {{ toast.message }}
      </p>
    </div>

    <!-- Close Button -->
    <button
      @click="handleRemove"
      class="flex-shrink-0 p-1 rounded-full hover:bg-black/10 dark:hover:bg-white/10 transition-colors"
    >
      <X class="w-4 h-4" />
    </button>
  </div>
</template>