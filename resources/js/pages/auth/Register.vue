<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    WalletIcon, 
    EyeIcon, 
    EyeOffIcon, 
    LoaderIcon,
    ArrowLeftIcon,
    UserIcon,
    MailIcon,
    LockIcon 
} from 'lucide-vue-next';
import { useDarkMode } from '@/composables/useDarkMode';

const { isDarkMode: isDark, toggle } = useDarkMode();

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const processing = ref(false);
const errors = ref<Record<string, string>>({});

const submit = async () => {
    processing.value = true;
    errors.value = {};
    
    try {
        await router.post('/register', form.value);
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

const toggleConfirmPassword = () => {
    showConfirmPassword.value = !showConfirmPassword.value;
};
</script>

<template>
    <Head title="Daftar - WalletQ" />
    
    <div class="min-h-screen bg-gradient-to-br from-coral-50 via-white to-teal-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 flex items-center justify-center p-4">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25% 75%, #fb7185 0%, transparent 50%), radial-gradient(circle at 75% 25%, #14b8a6 0%, transparent 50%)"></div>
        </div>

        <div class="relative w-full max-w-md">
            <!-- Back Button -->
            <Link href="/" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-coral-600 dark:hover:text-coral-400 mb-6 transition-colors">
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Kembali
            </Link>

            <!-- Register Card -->
            <div class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/30 p-8">
                <!-- Logo & Title -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-coral-500 to-coral-600 rounded-2xl mb-4 shadow-lg shadow-coral-500/25">
                        <WalletIcon class="w-8 h-8 text-white" />
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        Bergabung dengan WalletQ
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Buat akun baru untuk mengelola keuangan Anda
                    </p>
                </div>

                <!-- Register Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nama Lengkap
                        </label>
                        <div class="relative">
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="Masukkan nama lengkap"
                                class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-coral-500 focus:border-transparent transition-colors text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                                :class="{ 'border-red-300 dark:border-red-600 focus:ring-red-500': errors.name }"
                            />
                            <UserIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
                        </div>
                        <p v-if="errors.name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ errors.name }}
                        </p>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email
                        </label>
                        <div class="relative">
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autocomplete="email"
                                placeholder="nama@email.com"
                                class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-coral-500 focus:border-transparent transition-colors text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                                :class="{ 'border-red-300 dark:border-red-600 focus:ring-red-500': errors.email }"
                            />
                            <MailIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
                        </div>
                        <p v-if="errors.email" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ errors.email }}
                        </p>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                placeholder="Masukkan password"
                                class="w-full pl-12 pr-12 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-coral-500 focus:border-transparent transition-colors text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                                :class="{ 'border-red-300 dark:border-red-600 focus:ring-red-500': errors.password }"
                            />
                            <LockIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
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

                    <!-- Confirm Password Field -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Konfirmasi Password
                        </label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                placeholder="Konfirmasi password"
                                class="w-full pl-12 pr-12 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-coral-500 focus:border-transparent transition-colors text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                                :class="{ 'border-red-300 dark:border-red-600 focus:ring-red-500': errors.password_confirmation }"
                            />
                            <LockIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
                            <button
                                type="button"
                                @click="toggleConfirmPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                            >
                                <EyeIcon v-if="!showConfirmPassword" class="w-5 h-5" />
                                <EyeOffIcon v-else class="w-5 h-5" />
                            </button>
                        </div>
                        <p v-if="errors.password_confirmation" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ errors.password_confirmation }}
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full bg-gradient-to-r from-coral-500 to-coral-600 hover:from-coral-600 hover:to-coral-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-coral-500/25 hover:shadow-xl hover:shadow-coral-500/30 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
                    >
                        <LoaderIcon v-if="processing" class="w-5 h-5 animate-spin" />
                        <span>{{ processing ? 'Membuat akun...' : 'Buat Akun' }}</span>
                    </button>
                </form>

                <!-- Login Link -->
                <div class="mt-8 text-center">
                    <p class="text-gray-600 dark:text-gray-400">
                        Sudah punya akun?
                        <Link
                            href="/login"
                            class="text-coral-600 dark:text-coral-400 hover:text-coral-700 dark:hover:text-coral-300 font-medium transition-colors ml-1"
                        >
                            Masuk sekarang
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
