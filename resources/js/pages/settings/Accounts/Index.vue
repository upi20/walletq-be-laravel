<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
  CreditCard, 
  Plus, 
  Search, 
  Edit, 
  Eye, 
  Trash2,
  MoreVertical,
  ArrowLeft,
  Filter,
  SortAsc
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface PaginatedData<T> {
  data: T[];
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

interface Account {
  id: number;
  name: string;
  description: string | null;
  initial_balance: number;
  is_active: boolean;
  category: {
    id: number;
    name: string;
  };
  created_at: string;
}

interface AccountCategory {
  id: number;
  name: string;
}

interface Props {
  accounts: PaginatedData<Account>;
  categories: AccountCategory[];
  filters: {
    search?: string;
  };
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Settings',
    href: '/settings',
  },
  {
    title: 'Accounts',
    href: '/settings/accounts',
  },
];

const searchQuery = ref(props.filters.search || '');

const search = () => {
  router.get('/settings/accounts', { search: searchQuery.value }, { 
    preserveState: true,
    replace: true 
  });
};

const deleteAccount = (account: Account) => {
  if (confirm(`Are you sure you want to delete "${account.name}"?`)) {
    router.delete(`/settings/accounts/${account.id}`);
  }
};

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(amount);
};
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Accounts" />

    <SettingsLayout>
      <div class="flex flex-col space-y-6">
        <div class="flex items-center justify-between">
          <HeadingSmall 
            title="Accounts" 
            description="Manage your financial accounts" 
          />
          <Button as-child>
            <Link href="/settings/accounts/create">
              <Plus class="h-4 w-4 mr-2" />
              Add Account
            </Link>
          </Button>
        </div>

        <!-- Search -->
        <div class="flex items-center space-x-2">
          <div class="relative flex-1 max-w-sm">
            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input
              v-model="searchQuery"
              placeholder="Search accounts..."
              class="pl-8"
              @keyup.enter="search"
            />
          </div>
          <Button variant="outline" @click="search">
            Search
          </Button>
        </div>

        <!-- Table -->
        <div class="rounded-md border overflow-hidden">
          <table class="w-full">
            <thead class="border-b bg-muted/50">
              <tr>
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Name</th>
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Category</th>
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Initial Balance</th>
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Status</th>
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground w-[100px]">Actions</th>
              </tr>
            </thead>
            <tbody class="[&_tr:last-child]:border-0">
              <tr v-if="accounts.data.length === 0">
                <td colspan="5" class="h-24 text-center text-muted-foreground">
                  No accounts found
                </td>
              </tr>
              <tr v-for="account in accounts.data" :key="account.id" class="border-b transition-colors hover:bg-muted/50">
                <td class="p-4 align-middle">
                  <div>
                    <div class="font-medium">{{ account.name }}</div>
                    <div v-if="account.description" class="text-sm text-muted-foreground">
                      {{ account.description }}
                    </div>
                  </div>
                </td>
                <td class="p-4 align-middle">
                  <Badge variant="outline">{{ account.category.name }}</Badge>
                </td>
                <td class="p-4 align-middle">
                  <span class="font-mono text-sm">
                    {{ formatCurrency(account.initial_balance) }}
                  </span>
                </td>
                <td class="p-4 align-middle">
                  <Badge :variant="account.is_active ? 'default' : 'secondary'">
                    {{ account.is_active ? 'Active' : 'Inactive' }}
                  </Badge>
                </td>
                <td class="p-4 align-middle">
                  <div class="flex items-center gap-2">
                    <Button variant="ghost" size="sm" as-child>
                      <Link :href="`/settings/accounts/${account.id}`">
                        <Eye class="h-4 w-4" />
                      </Link>
                    </Button>
                    <Button variant="ghost" size="sm" as-child>
                      <Link :href="`/settings/accounts/${account.id}/edit`">
                        <Edit class="h-4 w-4" />
                      </Link>
                    </Button>
                    <Button variant="ghost" size="sm" @click="deleteAccount(account)">
                      <Trash2 class="h-4 w-4" />
                    </Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="accounts.links.length > 3" class="flex justify-center">
          <nav class="flex items-center space-x-2">
            <template v-for="link in accounts.links" :key="link.label">
              <Link
                v-if="link.url"
                :href="link.url"
                class="px-3 py-2 text-sm border rounded-md transition-colors"
                :class="{
                  'bg-primary text-primary-foreground': link.active,
                  'bg-background hover:bg-muted': !link.active
                }"
                v-html="link.label"
              />
              <span
                v-else
                class="px-3 py-2 text-sm border rounded-md text-muted-foreground cursor-not-allowed"
                v-html="link.label"
              />
            </template>
          </nav>
        </div>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>