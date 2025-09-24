<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    WalletIcon, 
    EyeIcon, 
    EyeOffIcon, 
    LoaderIcon,
    ArrowLeftIcon 
} from 'lucide-vue-next';
import { useDarkMode } from '@/composables/useDarkMode';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const { isDarkMode: isDark, toggle } = useDarkMode();

const form = ref({
    email: '',
    password: '',
    remember: false
});

const showPassword = ref(false);
const processing = ref(false);
const errors = ref<Record<string, string>>({});

const submit = async () => {
    processing.value = true;
    errors.value = {};
    
    try {
        await router.post('/login', form.value);
    } catch (error: any) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        }
    } finally {
        processing.value = false;
    }
};

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <Head title="Masuk - WalletQ" />
    
    <div class="min-h-screen bg-gradient-to-br from-teal-50 via-white to-coral-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 flex items-center justify-center p-4">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25% 25%, #14b8a6 0%, transparent 50%), radial-gradient(circle at 75% 75%, #fb7185 0%, transparent 50%)"></div>
        </div>

        <div class="relative w-full max-w-md">
            <!-- Back Button -->
            <Link href="/" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-teal-600 dark:hover:text-teal-400 mb-6 transition-colors">
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Kembali
            </Link>

            <!-- Login Card -->
            <div class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/30 p-8">
                <!-- Logo & Title -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl mb-4 shadow-lg shadow-teal-500/25">
                        <WalletIcon class="w-8 h-8 text-white" />
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        Selamat Datang Kembali
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Masuk ke akun WalletQ Anda
                    </p>
                </div>

                <!-- Status Message -->
                <div v-if="status" class="mb-6 p-4 bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800 rounded-xl text-teal-700 dark:text-teal-300 text-sm text-center">
                    {{ status }}
                </div>

                <!-- Login Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="nama@email.com"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-colors text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                            :class="{ 'border-red-300 dark:border-red-600 focus:ring-red-500': errors.email }"
                        />
                        <p v-if="errors.email" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ errors.email }}
                        </p>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Password
                            </label>
                            <Link
                                v-if="canResetPassword"
                                href="/forgot-password"
                                class="text-sm text-teal-600 dark:text-teal-400 hover:text-teal-700 dark:hover:text-teal-300 transition-colors"
                            >
                                Lupa password?
                            </Link>
                        </div>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                placeholder="Masukkan password"
                                class="w-full px-4 py-3 pr-12 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-colors text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                                :class="{ 'border-red-300 dark:border-red-600 focus:ring-red-500': errors.password }"
                            />
                            <button
                                type="button"
                                @click="togglePassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                            >
                                <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                                <EyeOffIcon v-else class="w-5 h-5" />
                            </button>
                        </div>
                        <p v-if="errors.password" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ errors.password }}
                        </p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="w-4 h-4 text-teal-600 bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded focus:ring-teal-500 focus:ring-2"
                        />
                        <label for="remember" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Ingat saya
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-teal-500/25 hover:shadow-xl hover:shadow-teal-500/30 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
                    >
                        <LoaderIcon v-if="processing" class="w-5 h-5 animate-spin" />
                        <span>{{ processing ? 'Memproses...' : 'Masuk' }}</span>
                    </button>
                </form>

                <!-- Register Link -->
                <div class="mt-8 text-center">
                    <p class="text-gray-600 dark:text-gray-400">
                        Belum punya akun?
                        <Link
                            href="/register"
                            class="text-teal-600 dark:text-teal-400 hover:text-teal-700 dark:hover:text-teal-300 font-medium transition-colors ml-1"
                        >
                            Daftar sekarang
                        </Link>
                    </p>
                </div>
            </div>

            <!-- Dark Mode Toggle -->
            <div class="flex justify-center mt-6">
                <button
                    @click="toggle"
                    class="p-2 rounded-xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 hover:bg-white/70 dark:hover:bg-gray-800/70 transition-colors"
                >
                    <span class="text-2xl">{{ isDark ? '☀️' : '🌙' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
