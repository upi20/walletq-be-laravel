<script setup lang="ts">
import { computed, onMounted, ref, nextTick } from 'vue';
import { 
  AlertTriangle,
  Info,
  CheckCircle,
  XCircle,
  X
} from 'lucide-vue-next';
import type { ConfirmationDialog } from '@/types/confirmation';

interface Props {
  dialog: ConfirmationDialog;
}

interface Emits {
  (e: 'confirm'): void;
  (e: 'cancel'): void;
  (e: 'close'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const isVisible = ref(false);
const confirmButtonRef = ref<HTMLButtonElement>();

const iconComponent = computed(() => {
  if (!props.dialog.options.showIcon) return null;
  
  switch (props.dialog.options.type) {
    case 'danger': return XCircle;
    case 'warning': return AlertTriangle;
    case 'success': return CheckCircle;
    case 'info': return Info;
    default: return Info;
  }
});

const iconClasses = computed(() => {
  switch (props.dialog.options.type) {
    case 'danger': return 'text-red-500 dark:text-red-400';
    case 'warning': return 'text-yellow-500 dark:text-yellow-400';
    case 'success': return 'text-green-500 dark:text-green-400';
    case 'info': return 'text-blue-500 dark:text-blue-400';
    default: return 'text-blue-500 dark:text-blue-400';
  }
});

const confirmButtonClasses = computed(() => {
  if (props.dialog.options.confirmButtonClass) {
    return props.dialog.options.confirmButtonClass;
  }
  
  switch (props.dialog.options.type) {
    case 'danger':
      return 'bg-red-600 hover:bg-red-700 focus:ring-red-500 text-white';
    case 'warning':
      return 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500 text-white';
    case 'success':
      return 'bg-green-600 hover:bg-green-700 focus:ring-green-500 text-white';
    case 'info':
      return 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 text-white';
    default:
      return 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 text-white';
  }
});

const cancelButtonClasses = computed(() => {
  return props.dialog.options.cancelButtonClass || 
    'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-100';
});

const handleConfirm = () => {
  emit('confirm');
};

const handleCancel = () => {
  emit('cancel');
};

const handleClose = () => {
  if (!props.dialog.options.persistent) {
    emit('close');
  }
};

const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape' && !props.dialog.options.persistent) {
    handleClose();
  } else if (event.key === 'Enter') {
    handleConfirm();
  }
};

onMounted(async () => {
  document.addEventListener('keydown', handleKeydown);
  
  await nextTick();
  isVisible.value = true;
  
  // Focus on confirm button for accessibility
  setTimeout(() => {
    confirmButtonRef.value?.focus();
  }, 100);
});

// Cleanup
const cleanup = () => {
  document.removeEventListener('keydown', handleKeydown);
};

// Watch for component unmount
import { onBeforeUnmount } from 'vue';
onBeforeUnmount(cleanup);
</script>

<template>
  <!-- Backdrop -->
  <div 
    :class="[
      'fixed inset-0 z-50 flex items-center justify-center p-4 transition-all duration-300',
      isVisible ? 'opacity-100' : 'opacity-0'
    ]"
    @click="handleClose"
  >
    <!-- Backdrop blur -->
    <div class="absolute inset-0 bg-black/50 dark:bg-black/70 backdrop-blur-sm"></div>
    
    <!-- Dialog -->
    <div 
      :class="[
        'relative w-full max-w-md transform transition-all duration-300',
        isVisible ? 'scale-100 translate-y-0' : 'scale-95 translate-y-4'
      ]"
      @click.stop
    >
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700">
        <!-- Header -->
        <div class="p-6 pb-4">
          <div class="flex items-start gap-4">
            <!-- Icon -->
            <div 
              v-if="iconComponent"
              class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center"
              :class="{
                'bg-red-100 dark:bg-red-900/20': dialog.options.type === 'danger',
                'bg-yellow-100 dark:bg-yellow-900/20': dialog.options.type === 'warning',
                'bg-green-100 dark:bg-green-900/20': dialog.options.type === 'success',
                'bg-blue-100 dark:bg-blue-900/20': dialog.options.type === 'info'
              }"
            >
              <component 
                :is="iconComponent" 
                :class="['w-6 h-6', iconClasses]" 
              />
            </div>
            
            <!-- Content -->
            <div class="flex-1 min-w-0">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                {{ dialog.options.title }}
              </h3>
              <p 
                v-if="dialog.options.message"
                class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed"
              >
                {{ dialog.options.message }}
              </p>
            </div>
            
            <!-- Close button (only if not persistent) -->
            <button
              v-if="!dialog.options.persistent"
              @click="handleClose"
              class="flex-shrink-0 p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            >
              <X class="w-5 h-5 text-gray-400" />
            </button>
          </div>
        </div>
        
        <!-- Actions -->
        <div class="px-6 pb-6">
          <div class="flex gap-3" :class="dialog.options.cancelText ? 'justify-end' : 'justify-center'">
            <!-- Cancel button -->
            <button
              v-if="dialog.options.cancelText"
              @click="handleCancel"
              :class="[
                'px-4 py-2 text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800',
                cancelButtonClasses
              ]"
            >
              {{ dialog.options.cancelText }}
            </button>
            
            <!-- Confirm button -->
            <button
              ref="confirmButtonRef"
              @click="handleConfirm"
              :class="[
                'px-4 py-2 text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800',
                confirmButtonClasses
              ]"
            >
              {{ dialog.options.confirmText }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>