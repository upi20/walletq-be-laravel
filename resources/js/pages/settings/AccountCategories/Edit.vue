<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
  Folder, 
  ArrowLeft,
  Save
} from 'lucide-vue-next';
import FinancialAppLayout from '@/layouts/FinancialAppLayout.vue';
import { useTranslation } from '@/composables/useTranslation';
import { useToast } from '@/composables/useToast';
import { useConfirmation } from '@/composables/useConfirmation';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import { AccountCategory } from '@/types';

// Translation
const { trans } = useTranslation();

// Toast notifications
const { success, error, warning, info } = useToast();

// Confirmation dialogs
const { confirmAction, alert } = useConfirmation();

interface Props {
  category: AccountCategory;
}

const props = defineProps<Props>();

const form = useForm({
  name: props.category.name || '',
  type: props.category.type || '',
});

const submit = async () => {
  form.put(`/settings/account-categories/${props.category.id}`, {
    onStart: () => {
      // info('Menyimpan kategori...', {
      //   message: 'Sedang memproses perubahan kategori.',
      //   duration: 2000
      // });
    },
    onSuccess: () => {
      success('Kategori berhasil diperbarui!', {
        message: 'Perubahan pada kategori rekening telah disimpan.',
        duration: 5000
      });
    },
    onError: (errors) => {
      // Show specific error messages for validation
      if (errors.name) {
        error('Nama kategori diperlukan', {
          message: 'Silakan masukkan nama untuk kategori.',
          duration: 4000
        });
      } else {
        error('Gagal memperbarui kategori', {
          message: 'Terjadi kesalahan saat menyimpan perubahan. Silakan coba lagi.',
          persistent: true
        });
      }
    }
  });
};

</script>

<template>
  <FinancialAppLayout :showHeader="false" :showFab="false">
    <Head title="Edit Kategori Rekening" />

    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <Link
          href="/settings/account-categories"
          class="inline-flex items-center text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300 transition-colors mb-4"
        >
          <ArrowLeft class="w-4 h-4 mr-2" />
          Kembali ke Daftar Kategori
        </Link>
        <LanguageSwitcher />
      </div>
      
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 bg-gradient-to-r from-teal-500 to-coral-500 rounded-xl flex items-center justify-center">
          <Folder class="w-5 h-5 text-white" />
        </div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-teal-600 to-coral-600 bg-clip-text text-transparent">
          Edit Kategori Rekening
        </h1>
      </div>
      <p class="text-gray-600 dark:text-gray-300">
        Edit detail kategori rekening. Pastikan informasi yang Anda masukkan sudah benar.
      </p>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Category Name -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Nama Kategori
            <span class="text-red-500">*</span>
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            placeholder="Masukkan nama kategori rekening"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            :class="{ 'border-red-500': form.errors.name }"
          />
          <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.name }}
          </p>
        </div>

        <!-- Type -->
        <div>
          <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Jenis Kategori
          </label>
          <select
            id="type"
            v-model="form.type"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            :class="{ 'border-red-500': form.errors.type }"
          >
            <option value="">Pilih jenis kategori (opsional)</option>
            <option value="cash">Tunai</option>
            <option value="bank">Bank</option>
            <option value="e-wallet">E-Wallet</option>
          </select>
          <p v-if="form.errors.type" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.type }}
          </p>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Pilih jenis kategori untuk memudahkan pengelompokan rekening Anda
          </p>
        </div>

        <!-- Submit Button -->
        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
          <div class="flex gap-4">
            <button
              type="submit"
              :disabled="form.processing"
              class="flex-1 flex items-center justify-center px-6 py-3 bg-gradient-to-r from-teal-600 to-coral-600 text-white rounded-xl font-medium hover:from-teal-700 hover:to-coral-700 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <Save class="w-5 h-5 mr-2" />
              {{ form.processing ? 'Menyimpan...' : 'Update Kategori' }}
            </button>
            
            <Link
              href="/settings/account-categories"
              class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              Batal
            </Link>
          </div>
        </div>
      </form>
    </div>
  </FinancialAppLayout>
</template>