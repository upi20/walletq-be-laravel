<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import { Search, X } from 'lucide-vue-next';

interface Props {
  initialQuery?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  apply: [query: string];
  close: [];
}>();

const searchQuery = ref(props.initialQuery || '');
const searchInput = ref<HTMLInputElement>();

const handleSubmit = () => {
  emit('apply', searchQuery.value);
};

const handleClose = () => {
  emit('close');
};

const handleKeyDown = (event: KeyboardEvent) => {
  if (event.key === 'Enter') {
    handleSubmit();
  } else if (event.key === 'Escape') {
    handleClose();
  }
};

onMounted(async () => {
  await nextTick();
  searchInput.value?.focus();
});
</script>

<template>
  <!-- Modal Overlay -->
  <div 
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    @click.self="handleClose"
  >
    <!-- Modal Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-auto border border-gray-200 dark:border-gray-700">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-2">
          <Search class="w-5 h-5 text-teal-600 dark:text-teal-400" />
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Cari Transaksi</h3>
        </div>
        
        <button
          @click="handleClose"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Search Input -->
      <div class="p-4">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
          <input
            ref="searchInput"
            v-model="searchQuery"
            @keydown="handleKeyDown"
            type="text"
            placeholder="Ketik untuk mencari transaksi..."
            class="w-full pl-12 pr-4 py-4 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent text-base"
          />
        </div>
        
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
          Cari berdasarkan catatan, nama akun, atau kategori transaksi
        </p>
      </div>

      <!-- Footer Actions -->
      <div class="flex gap-3 p-4 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
        <button
          @click="searchQuery = ''"
          class="flex-1 py-3 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 font-medium text-sm"
        >
          Hapus
        </button>
        
        <button
          @click="handleSubmit"
          class="flex-1 py-3 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-xl hover:from-teal-600 hover:to-teal-700 transition-all duration-200 font-medium text-sm"
        >
          Cari
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Ensure modal appears above everything */
.z-50 {
  z-index: 50;
}
</style>
