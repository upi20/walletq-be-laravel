<script setup lang="ts">
import { computed, reactive } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { useDarkMode } from '@/composables/useDarkMode';
import { 
  Home, 
  TrendingUp, 
  PieChart, 
  Settings, 
  Bell,
  Plus,
  Moon,
  Sun
} from 'lucide-vue-next';
import formatCurrency from '@/composables/formatCurrency';
import ToastContainer from '@/components/Toast/ToastContainer.vue';
import ConfirmationContainer from '@/components/Confirmation/ConfirmationContainer.vue';

interface Props {
  showHeader?: boolean;
  showBottomNav?: boolean;
  showFab?: boolean;
  containerClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  showHeader: true,
  showBottomNav: true,
  showFab: true,
  containerClass: 'px-6',
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const { isDarkMode, toggle: toggleDarkMode } = useDarkMode();

// Mock data untuk sekarang - nanti akan diganti dengan data real
const totalBalance = page.props.auth?.user.balance || 0;

const greeting = computed(() => {
  const hour = new Date().getHours();
  if (hour < 12) return 'Good Morning';
  if (hour < 18) return 'Good Afternoon';
  return 'Good Evening';
});

const bottomNavItems = reactive([
  { icon: Home, label: 'Home', route: 'dashboard', active: false },
  { icon: TrendingUp, label: 'Transactions', route: 'transactions', active: false },
  { icon: PieChart, label: 'Reports', route: 'reports', active: false },
  { icon: Settings, label: 'Settings', route: 'settings', active: false },
]);


// Set active state based on current route
const currentRoute = page.url.split('/')[1] || 'dashboard';
bottomNavItems.forEach(item => {
  item.active = item.route === currentRoute;
});

</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-300 max-w-[640px] mx-auto">
    <!-- Curved Header dengan Gradient -->
    <header 
      v-if="showHeader" 
      class="relative h-70 bg-gradient-to-br from-teal-500 to-teal-600 dark:from-teal-700 dark:to-teal-800 rounded-b-8 px-6 pt-12 pb-6"
    >
      <!-- Top Section: Greeting & Actions -->
      <div class="flex justify-between items-start mb-8">
        <div>
          <p class="text-white/80 text-base font-normal">
            {{ greeting }},
          </p>
          <h1 class="text-white text-2xl font-semibold tracking-wide">
            {{ user?.name || 'User' }}
          </h1>
        </div>
        
        <!-- Top Actions -->
        <div class="flex items-center space-x-3">
          <!-- Dark Mode Toggle -->
          <button 
            @click="toggleDarkMode"
            class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors duration-200"
          >
            <Sun v-if="isDarkMode" class="w-5 h-5 text-white" />
            <Moon v-else class="w-5 h-5 text-white" />
          </button>
          
          <!-- Notification Icon -->
          <div class="relative">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
              <Bell class="w-5 h-5 text-white" />
            </div>
            <!-- Badge -->
            <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center">
              <span class="text-white text-xs font-medium">3</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Balance Section -->
      <div class="text-center">
        <p class="text-white/80 text-base font-normal mb-1">
          Total Balance
        </p>
        <h2 class="text-white text-4xl font-bold tracking-tight">
          {{ formatCurrency(totalBalance) }}
        </h2>
      </div>
    </header>

    <!-- Floating Tab Navigation -->
    <nav 
      v-if="showHeader" 
      class="relative -mt-6 mx-6 mb-8">
      <div class="bg-white dark:bg-gray-800 rounded-full h-12 shadow-lg flex items-center p-1 transition-colors duration-300">
        <button class="flex-1 h-10 bg-gradient-to-r from-teal-500 to-teal-600 dark:from-teal-600 dark:to-teal-700 text-white rounded-full text-sm font-semibold transition-all duration-300">
          Overview
        </button>
        <button class="flex-1 h-10 text-gray-500 dark:text-gray-400 rounded-full text-sm font-medium transition-all duration-300 hover:text-gray-700 dark:hover:text-gray-300">
          Expenses
        </button>
        <button class="flex-1 h-10 text-gray-500 dark:text-gray-400 rounded-full text-sm font-medium transition-all duration-300 hover:text-gray-700 dark:hover:text-gray-300">
          Income
        </button>
      </div>
    </nav>

    <!-- Main Content -->
    <main :class="showHeader ? '' : 'pt-6' + 'pb-20' + ' ' + containerClass">
      <slot />
    </main>

    <!-- Bottom Navigation -->
    <nav 
      v-if="showBottomNav" 
      class="fixed bottom-0 left-0 right-0 max-w-[640px] mx-auto bg-white dark:bg-gray-900 shadow-sm border-gray-200 dark:border-gray-700 h-16 flex items-center justify-around px-4 transition-colors duration-300"
    >
      <Link 
        v-for="item in bottomNavItems" 
        :key="item.route"
        :href="`/${item.route}`"
        class="flex flex-col items-center justify-center w-12 h-12 transition-colors duration-200"
        :class="item.active ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400'"
        :title="item.label"
        :tooltip="item.label"
      >
        <component :is="item.icon" class="w-6 h-6" />
        <!-- <span class="text-xs mt-1">{{ item.label }}</span> -->
      </Link>
    </nav>

    <!-- Floating Action Button -->
    <button 
      v-if="showFab"
      class="fixed bottom-20 right-6 w-14 h-14 bg-gradient-to-r from-teal-500 to-teal-600 dark:from-teal-600 dark:to-teal-700 rounded-full shadow-xl flex items-center justify-center transition-all duration-300 hover:scale-105 active:scale-95"
    >
      <Plus class="w-6 h-6 text-white" />
    </button>

    <!-- Toast Container -->
    <ToastContainer />
    
    <!-- Confirmation Container -->
    <ConfirmationContainer />
  </div>
</template>

<style scoped>
/* Custom styles untuk curved header jika diperlukan */
.rounded-b-8 {
  border-bottom-left-radius: 2rem;
  border-bottom-right-radius: 2rem;
}

.h-70 {
  height: 17.5rem; /* 280px */
}
</style>