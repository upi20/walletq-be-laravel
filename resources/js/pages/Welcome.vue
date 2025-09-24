<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { useDarkMode } from '@/composables/useDarkMode';
import { WalletIcon, TrendingUpIcon, ShieldCheckIcon, SmartphoneIcon } from 'lucide-vue-next';

const { isDarkMode: isDark, toggle } = useDarkMode();
</script>

<template>
    <Head title="Welcome to WalletQ - Personal Finance Manager" />
    
    <div class="min-h-screen bg-gradient-to-br from-teal-50 to-coral-50 dark:from-gray-900 dark:to-gray-800">
        <!-- Navigation Header -->
        <header class="relative z-10">
            <nav class="container mx-auto px-6 py-6 flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl flex items-center justify-center">
                        <WalletIcon class="w-6 h-6 text-white" />
                    </div>
                    <span class="text-xl font-bold text-gray-900 dark:text-white">WalletQ</span>
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center space-x-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-xl transition-colors duration-200"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-teal-600 dark:hover:text-teal-400 font-medium transition-colors duration-200"
                        >
                            Masuk
                        </Link>
                        <Link
                            :href="register()"
                            class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-xl transition-colors duration-200"
                        >
                            Daftar Gratis
                        </Link>
                    </template>
                    <button
                        @click="toggle"
                        class="p-2 rounded-xl bg-white/0 dark:bg-gray-800/0 backdrop-blur-sm border border-gray-200/0 dark:border-gray-700/0 hover:bg-white/70 dark:hover:bg-gray-800/70 transition-colors"
                    >
                        <span class="text-2xl">{{ isDark ? '☀️' : '🌙' }}</span>
                    </button>
                </div>
            </nav>
        </header>
        <!-- Hero Section -->
        <main class="container mx-auto px-6 py-20">
            <div class="text-center max-w-4xl mx-auto">
                <h1 class="text-5xl md:text-6xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
                    Kelola Keuangan dengan
                    <span class="bg-gradient-to-r from-teal-600 to-coral-500 bg-clip-text text-transparent">
                        Lebih Mudah
                    </span>
                </h1>
                
                <p class="text-xl text-gray-600 dark:text-gray-300 mb-12 max-w-2xl mx-auto leading-relaxed">
                    WalletQ membantu Anda mengelola keuangan pribadi dengan fitur lengkap dan tampilan yang mudah digunakan. 
                    Pantau pemasukan, pengeluaran, dan rencana keuangan Anda dalam satu aplikasi.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-16">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="register()"
                        class="inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white font-semibold rounded-2xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                    >
                        Mulai Gratis Sekarang
                    </Link>
                    <Link
                        v-else
                        :href="dashboard()"
                        class="inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white font-semibold rounded-2xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                    >
                        Buka Dashboard
                    </Link>
                    <button 
                        class="inline-flex items-center justify-center px-8 py-4 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-teal-500 hover:text-teal-600 dark:hover:text-teal-400 font-semibold rounded-2xl transition-colors duration-200"
                    >
                        Lihat Demo
                    </button>
                </div>

                <!-- Features Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <!-- Feature 1 -->
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-200">
                        <div class="w-14 h-14 bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                            <TrendingUpIcon class="w-7 h-7 text-white" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            Analisis Keuangan
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                            Dapatkan insight mendalam tentang pola pengeluaran dan pemasukan Anda dengan grafik dan laporan yang mudah dipahami.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-200">
                        <div class="w-14 h-14 bg-gradient-to-br from-coral-500 to-coral-600 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                            <ShieldCheckIcon class="w-7 h-7 dark:text-white" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            Aman & Terpercaya
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                            Data keuangan Anda tersimpan dengan aman menggunakan enkripsi tingkat bank dan backup otomatis.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-200">
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                            <SmartphoneIcon class="w-7 h-7 text-white" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            Akses Dimana Saja
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                            Kelola keuangan Anda kapan saja dan dimana saja melalui aplikasi web yang responsif dan mudah digunakan.
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Stats Section -->
        <section class="bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm py-16">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center max-w-4xl mx-auto">
                    <div>
                        <div class="text-3xl font-bold text-teal-600 mb-2">1000+</div>
                        <div class="text-gray-600 dark:text-gray-300">Pengguna Aktif</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-coral-500 mb-2">50K+</div>
                        <div class="text-gray-600 dark:text-gray-300">Transaksi Tercatat</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-purple-600 mb-2">99.9%</div>
                        <div class="text-gray-600 dark:text-gray-300">Uptime</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-teal-600 mb-2">24/7</div>
                        <div class="text-gray-600 dark:text-gray-300">Support</div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Footer -->
        <footer class="bg-gray-900 dark:bg-black py-12">
            <div class="container mx-auto px-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center space-x-3 mb-6 md:mb-0">
                        <div class="w-8 h-8 bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl flex items-center justify-center">
                            <WalletIcon class="w-5 h-5 text-white" />
                        </div>
                        <span class="text-lg font-semibold text-white">WalletQ</span>
                    </div>
                    
                    <div class="text-sm text-gray-400 text-center">
                        © 2025 WalletQ. Semua hak dilindungi.
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
