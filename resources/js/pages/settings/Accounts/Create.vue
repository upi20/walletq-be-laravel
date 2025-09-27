<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
  CreditCard, 
  ArrowLeft,
  Save,
  DollarSign
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
  categories: AccountCategory[];
}

const props = defineProps<Props>();

const form = useForm({
  name: '',
  note: '',
  account_category_id: '',
  initial_balance: 0,
  // is_active: true,
});

const submit = async () => {
  // Confirm important action before submitting
  if (form.initial_balance > 10000000) { // Jika saldo awal > 10 juta
    const confirmed = await confirmAction(
      'Konfirmasi Saldo Besar',
      `Anda akan membuat akun dengan saldo awal ${formatNumber(form.initial_balance.toString())}. Apakah data ini sudah benar?`
    );
    
    if (!confirmed) {
      return;
    }
  }
  
  form.post('/settings/accounts', {
    onStart: () => {
      // info('Menyimpan akun...', {
      //   message: 'Sedang memproses data akun baru.',
      //   duration: 2000
      // });
    },
    onSuccess: () => {
      success('Akun berhasil dibuat!', {
        message: 'Akun baru telah ditambahkan ke daftar akun Anda.',
        duration: 5000
      });
    },
    onError: (errors) => {
      // Show specific error messages for validation
      if (errors.name) {
        error('Nama akun diperlukan', {
          message: 'Silakan masukkan nama untuk akun baru.',
          duration: 4000
        });
      } else if (errors.account_category_id) {
        error('Kategori akun diperlukan', {
          message: 'Silakan pilih kategori untuk akun baru.',
          duration: 4000
        });
      } else {
        error('Gagal membuat akun', {
          message: 'Terjadi kesalahan saat menyimpan data akun. Silakan coba lagi.',
          persistent: true
        });
      }
    }
  });
};

const formatNumber = (value: string) => {
  const number = parseFloat(value.replace(/[^0-9.-]/g, ''));
  return isNaN(number) ? 0 : number;
};

// Tambahkan fungsi untuk format rupiah
const formatRupiah = (value: number | string) => {
  const num = typeof value === 'string' ? parseFloat(value) || 0 : value;
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

// Tambahkan fungsi untuk handle input
const handleBalanceInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const rawValue = target.value.replace(/\./g, '');
  const numericValue = parseFloat(rawValue) || 0;
  
  form.initial_balance = numericValue;
  target.value = formatRupiah(numericValue);
};
</script>

<template>
  <FinancialAppLayout :showHeader="false" :showFab="false" containerClass="px-3 pt-6 pb-32">
    <Head title="Create Account" />

    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <Link
          href="/settings/accounts"
          class="inline-flex items-center text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300 transition-colors mb-4"
        >
          <ArrowLeft class="w-4 h-4 mr-2" />
          {{ trans('settings.items.accounts.back_to_list') }}
        </Link>
        <LanguageSwitcher />
      </div>
      
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 bg-gradient-to-r from-teal-500 to-coral-500 rounded-xl flex items-center justify-center">
          <CreditCard class="w-5 h-5 text-white" />
        </div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-teal-600 to-coral-600 bg-clip-text text-transparent">
          {{ trans('settings.items.accounts.title_create') }}
        </h1>
      </div>
      <p class="text-gray-600 dark:text-gray-300">
        {{ trans('settings.items.accounts.note_create') }}
      </p>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Account Name -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ trans('settings.items.accounts.form.name') }} 
            <span class="text-red-500">*</span>
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            :placeholder="trans('settings.items.accounts.form.name_placeholder')"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            :class="{ 'border-red-500': form.errors.name }"
          />
          <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.name }}
          </p>
        </div>

        <!-- Category -->
        <div>
          <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ trans('settings.items.accounts.form.category') }}
            <span class="text-red-500">*</span>
          </label>
          <select
            id="category"
            v-model="form.account_category_id"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-transparent"
            :class="{ 'border-red-500': form.errors.account_category_id }"
          >
            <option value="">{{ trans('settings.items.accounts.form.category_first_option') }}</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
          <p v-if="form.errors.account_category_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.account_category_id }}
          </p>
        </div>

        <!-- Initial Balance -->
        <div>
          <label for="balance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ trans('settings.items.accounts.form.initial_balance') }}
          </label>
          <div class="relative">
            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
              Rp
            </div>
            <input
              id="balance"
              :value="formatRupiah(form.initial_balance)"
              @input="handleBalanceInput"
              type="text"
              class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-teal-500 focus:border-transparent"
              :class="{ 'border-red-500': form.errors.initial_balance }"
            />
          </div>
          <p v-if="form.errors.initial_balance" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.initial_balance }}
          </p>
        </div>

        <!-- Note -->
        <div>
          <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ trans('settings.items.accounts.form.note') }}
          </label>
          <textarea
            id="note"
            v-model="form.note"
            rows="3"
            :placeholder="trans('settings.items.accounts.form.note_placeholder')"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-teal-500 focus:border-transparent resize-none"
            :class="{ 'border-red-500': form.errors.note }"
          />
          <p v-if="form.errors.note" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.note }}
          </p>
        </div>

        <!-- Active Status -->
        <!-- <div class="flex items-center gap-3">
          <input
            id="is_active"
            v-model="form.is_active"
            type="checkbox"
            class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500 dark:focus:ring-teal-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
          />
          <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ trans('settings.items.accounts.form.is_active') }}
          </label>
        </div> -->

        <!-- Submit Button -->
        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
          <div class="flex gap-4">
            <button
              type="submit"
              :disabled="form.processing"
              class="flex-1 flex items-center justify-center px-6 py-3 bg-gradient-to-r from-teal-600 to-coral-600 text-white rounded-xl font-medium hover:from-teal-700 hover:to-coral-700 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <Save class="w-5 h-5 mr-2" />
              {{ form.processing ? trans('settings.items.accounts.btn.save_progress') : trans('settings.items.accounts.btn.save') }}
            </button>
            
            <Link
              href="/settings/accounts"
              class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              {{ trans('settings.items.accounts.btn.cancel') }}
            </Link>
          </div>
        </div>
      </form>
    </div>
  </FinancialAppLayout>
</template>