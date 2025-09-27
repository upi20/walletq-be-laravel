import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at?: string;
    updated_at?: string;
    balance: number;
}


// Model Data
export interface TransactionCategory {
    id: number;
    user_id: number | null;
    name: string;
    type: 'income' | 'expense';
    is_default: boolean;
    is_hide: boolean;
    created_at?: string;
    updated_at?: string;

    // Relations
    user?: User | null;
    transactions?: Transaction[] | null;
}

export interface AccountCategory {
    id: number;
    user_id: number | null;
    name: string;
    is_default: boolean;
    created_at?: string;
    updated_at?: string;

    // optional type added by migration
    type?: 'cash' | 'bank' | 'e-wallet' | null;

    // Relations
    user?: User | null;
    accounts?: Account[] | null;
}

export interface Account {
    id: number;
    user_id: number;
    name: string;
    // nullable fields from migrations
    account_category_id: number | null;
    type?: string | null; // migration had a string 'type' nullable
    note?: string | null;

    // balances
    initial_balance: number;
    current_balance: number;

    created_at?: string;
    updated_at?: string;

    // Relations
    user?: User | null;
    category?: AccountCategory | null;
    transactions?: Transaction[] | null;
}

export interface Transaction {
    id: number;
    import_id?: number | null;
    user_id?: number | null;
    account_id?: number | null;
    transaction_category_id?: number | null;

    type: 'income' | 'expense';
    amount: number;
    date: string; // datetime as ISO string
    note?: string | null;

    source_type?: string | null;
    source_id?: number | null;

    flag:
        | 'normal'
        | 'transfer_in'
        | 'transfer_out'
        | 'debt_payment'
        | 'debt_collect'
        | 'initial_balance';

    created_at?: string;
    updated_at?: string;

    // Relations
    user?: User | null;
    import?: ImportTransaction | null;
    account?: Account | null;
    category?: TransactionCategory | null;

    // polymorphic source: can be Transfer, Debt, ImportTransaction, etc.
    source?: Transfer | Debt | ImportTransaction | null;

    tags?: Tag[] | null;
}

export interface Transfer {
    id: number;
    user_id?: number | null;
    from_account_id?: number | null;
    to_account_id?: number | null;
    amount: number;
    date: string; // date only
    note?: string | null;
    created_at?: string;
    updated_at?: string;

    // Relations
    user?: User | null;
    fromAccount?: Account | null;
    toAccount?: Account | null;
    transactions?: Transaction[] | null;
    expenseTransaction?: Transaction | null;
    incomeTransaction?: Transaction | null;
}

export interface Debt {
    id: number;
    user_id?: number | null;
    contact_name: string;
    type: 'debt' | 'receivable';

    total_amount: number;
    paid_amount: number;

    status: 'unpaid' | 'partial' | 'paid';

    due_date?: string | null;
    note?: string | null;

    created_at?: string;
    updated_at?: string;

    // Relations
    user?: User | null;
    transactions?: Transaction[] | null;
}

export interface Tag {
    id: number;
    user_id?: number | null;
    name: string;
    created_at?: string;
    updated_at?: string;

    // Relations
    user?: User | null;
    transactions?: Transaction[] | null;
}

export interface TransactionTag {
    id: number;
    transaction_id?: number | null;
    tag_id?: number | null;

    // Relations
    transaction?: Transaction | null;
    tag?: Tag | null;
}

export interface ImportTransaction {
    id: number;
    file?: string | null;
    created_at?: string;
    updated_at?: string;

    // Relations
    transactions?: Transaction[] | null;
}

export type BreadcrumbItemType = BreadcrumbItem;

// Transaction List Response Types (Simplified)
export interface TransactionListResponse {
  data: Transaction[];
  summary: {
    total_income: number;
    total_expense: number;
    net_amount: number;
    transaction_count: number;
    month: string;
  };
  master_data: {
    accounts: Account[];
    income_categories: TransactionCategory[];
    expense_categories: TransactionCategory[];
    tags: Tag[];
    flag_options: Array<{ value: string; label: string; }>;
    type_options: Array<{ value: string; label: string; }>;
  };
  filters: {
    month?: string;
  };
}

