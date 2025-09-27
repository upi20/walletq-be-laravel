<?php

namespace App\Http\Controllers\API\User\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $filters = $this->validateAndPrepareFilters($request, $user);

        $query = $user->transactions()->with(['category', 'account', 'tags']);

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'date';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        if ($sortBy === 'date') {
            $query->orderBy('date', $sortOrder)->orderBy('created_at', $sortOrder);
        } elseif ($sortBy === 'amount') {
            $query->orderBy('amount', $sortOrder);
        } elseif ($sortBy === 'account') {
            $query->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                ->orderBy('accounts.name', $sortOrder)
                ->select('transactions.*');
        } elseif ($sortBy === 'category') {
            $query->join('transaction_categories', 'transactions.transaction_category_id', '=', 'transaction_categories.id')
                ->orderBy('transaction_categories.name', $sortOrder)
                ->select('transactions.*');
        }

        // Get all transactions without pagination
        $transactions = $query->get();

        return response()->json([
            'status' => 200,
            'message' => 'Transactions fetched successfully',
            'data' => $transactions,
            'meta' => [
                'total' => $transactions->count(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,id',
            'transaction_category_id' => 'required|exists:transaction_categories,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'note' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            Log::debug('Creating transaction', [
                'user_id' => $request->user()->id,
                'account_id' => $request->account_id,
                'amount' => $request->amount,
                'type' => $request->type
            ]);

            // Lock account for update to prevent race conditions
            $account = $request->user()->accounts()
                ->where('id', $request->account_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Check for sufficient balance if this is an expense
            if ($request->type === 'expense' && !$account->hasSufficientBalance($request->amount)) {
                DB::rollback();
                return response()->json([
                    'status' => 422,
                    'message' => 'Insufficient balance for this transaction'
                ], 422);
            }

            // Create and save the transaction first
            $transaction = new Transaction([
                'user_id' => $request->user()->id,
                'account_id' => $account->id,
                'transaction_category_id' => $request->transaction_category_id,
                'type' => $request->type,
                'amount' => $request->amount,
                'date' => $request->date,
                'note' => $request->note,
                'flag' => Transaction::FLAG_NORMAL
            ]);

            $transaction->save();

            // Update account balance
            $account->updateBalance($transaction->amount, $transaction->type);

            // Update user's total balance
            User::refreshBalance($request->user()->id);

            // Attach tags if any
            if ($request->has('tags')) {
                $transaction->tags()->attach($request->tags);
            }

            DB::commit();

            // Load relationships for response
            $transaction->load(['category', 'account', 'tags']);

            return response()->json([
                'status' => 201,
                'message' => 'Transaction created successfully',
                'data' => $transaction
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating transaction', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 500,
                'message' => 'Error creating transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transactions' => 'required|array|min:1',
            'transactions.*.account_id' => 'required|exists:accounts,id',
            'transactions.*.transaction_category_id' => 'required|exists:transaction_categories,id',
            'transactions.*.type' => 'required|in:income,expense',
            'transactions.*.amount' => 'required|numeric|min:0',
            'transactions.*.date' => 'required|date',
            'transactions.*.note' => 'nullable|string',
            'transactions.*.tags' => 'nullable|array',
            'transactions.*.tags.*' => 'exists:tags,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $createdTransactions = [];
            $accounts = collect();

            // First verify balances for all transactions
            foreach ($request->transactions as $transactionData) {
                $account = $accounts->get($transactionData['account_id']) ??
                    $request->user()->accounts()
                    ->where('id', $transactionData['account_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $accounts->put($transactionData['account_id'], $account);

                if ($transactionData['type'] === 'expense' && !$account->hasSufficientBalance($transactionData['amount'])) {
                    DB::rollback();
                    return response()->json([
                        'status' => 422,
                        'message' => "Insufficient balance in account {$account->name} for transaction of {$transactionData['amount']}"
                    ], 422);
                }
            }

            // Create all transactions and update balances
            foreach ($request->transactions as $transactionData) {
                $account = $accounts->get($transactionData['account_id']);

                $transaction = new Transaction([
                    'user_id' => $request->user()->id,
                    'account_id' => $account->id,
                    'transaction_category_id' => $transactionData['transaction_category_id'],
                    'type' => $transactionData['type'],
                    'amount' => $transactionData['amount'],
                    'date' => $transactionData['date'],
                    'note' => $transactionData['note'] ?? null,
                    'flag' => Transaction::FLAG_NORMAL
                ]);

                $transaction->save();

                // Update account balance
                $account->updateBalance($transaction->amount, $transaction->type);

                // Attach tags if any
                if (isset($transactionData['tags']) && is_array($transactionData['tags'])) {
                    $transaction->tags()->attach($transactionData['tags']);
                }

                $createdTransactions[] = $transaction;
            }

            // Update user's total balance after all transactions
            User::refreshBalance($request->user()->id);

            DB::commit();

            // Load relationships for response
            $transactions = collect($createdTransactions)->map(function ($transaction) {
                return $transaction->load(['category', 'account', 'tags']);
            });

            return response()->json([
                'status' => 201,
                'message' => 'Bulk transactions created successfully',
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 500,
                'message' => 'Error creating bulk transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $transaction = $request->user()->transactions()
            ->with(['category', 'account', 'tags'])
            ->find($id);

        if (!$transaction) {
            return response()->json([
                'status' => 404,
                'message' => 'Transaction not found'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Transaction fetched successfully',
            'data' => $transaction
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,id',
            'transaction_category_id' => 'required|exists:transaction_categories,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'note' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $transaction = $request->user()->transactions()->find($id);

        if (!$transaction) {
            return response()->json([
                'status' => 404,
                'message' => 'Transaction not found'
            ], 404);
        }

        if ($transaction->flag !== Transaction::FLAG_NORMAL) {
            return response()->json([
                'status' => 422,
                'message' => 'Cannot update system-generated transaction'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $transaction->account_id = $request->account_id;
            $transaction->transaction_category_id = $request->transaction_category_id;
            $transaction->type = $request->type;
            $transaction->amount = $request->amount;
            $transaction->date = $request->date;
            $transaction->note = $request->note;
            $transaction->save();

            // Sync tags
            if ($request->has('tags')) {
                $transaction->tags()->sync($request->tags);
            }

            User::refreshBalance($request->user()->id);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Transaction updated successfully',
                'data' => $transaction->load(['category', 'account', 'tags'])
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 500,
                'message' => 'Error updating transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $transaction = $request->user()->transactions()->find($id);

        if (!$transaction) {
            return response()->json([
                'status' => 404,
                'message' => 'Transaction not found'
            ], 404);
        }

        if ($transaction->flag !== Transaction::FLAG_NORMAL) {
            return response()->json([
                'status' => 422,
                'message' => 'Cannot delete system-generated transaction'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $transaction->tags()->detach();
            $transaction->delete();

            User::refreshBalance($request->user()->id);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Transaction deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 500,
                'message' => 'Error deleting transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate and prepare filter data from request for API
     */
    private function validateAndPrepareFilters(Request $request, $user): array
    {
        // Prepare data
        $data = $request->all();

        // Convert string arrays to arrays if needed
        if (isset($data['account_ids']) && is_string($data['account_ids'])) {
            $data['account_ids'] = explode(',', $data['account_ids']);
        }

        if (isset($data['category_ids']) && is_string($data['category_ids'])) {
            $data['category_ids'] = explode(',', $data['category_ids']);
        }

        if (isset($data['tag_ids']) && is_string($data['tag_ids'])) {
            $data['tag_ids'] = explode(',', $data['tag_ids']);
        }

        if (isset($data['flags']) && is_string($data['flags'])) {
            $data['flags'] = explode(',', $data['flags']);
        }

        // Handle backward compatibility with old API parameters
        if (isset($data['account_id'])) {
            $data['account_ids'] = [$data['account_id']];
        }
        if (isset($data['category_id'])) {
            $data['category_ids'] = [$data['category_id']];
        }
        if (isset($data['start_date'])) {
            $data['date_from'] = $data['start_date'];
        }
        if (isset($data['end_date'])) {
            $data['date_to'] = $data['end_date'];
        }

        // Set default values
        $data['type'] = $data['type'] ?? 'both';
        $data['sort_by'] = $data['sort_by'] ?? 'date';
        $data['sort_order'] = $data['sort_order'] ?? 'desc';
        $data['flags'] = $data['flags'] ?? [Transaction::FLAG_NORMAL];

        // Validation rules (more lenient for API)
        $rules = [
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'account_ids' => 'nullable|array',
            'account_ids.*' => 'integer|exists:accounts,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:transaction_categories,id',
            'type' => 'nullable|in:income,expense,both',
            'flags' => 'nullable|array',
            'flags.*' => 'string|in:' . implode(',', Transaction::FLAGS),
            'amount_min' => 'nullable|numeric|min:0',
            'amount_max' => 'nullable|numeric|min:0|gte:amount_min',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id',
            'search' => 'nullable|string|max:255',
            'sort_by' => 'nullable|in:date,amount,account,category',
            'sort_order' => 'nullable|in:asc,desc',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        $validated = $validator->validated();

        // Add user_id for security
        $validated['user_id'] = $user->id;

        // Remove empty arrays and null values
        return array_filter($validated, function ($value) {
            if (is_array($value)) {
                return !empty($value);
            }
            return $value !== null && $value !== '';
        });
    }

    /**
     * Apply filters to transaction query
     */
    private function applyFilters($query, array $filters): void
    {
        // Flag filters (default to normal transactions)
        if (isset($filters['flags'])) {
            $query->whereIn('flag', $filters['flags']);
        } else {
            $query->where('flag', Transaction::FLAG_NORMAL);
        }

        // Account filters
        if (isset($filters['account_ids'])) {
            $query->whereIn('account_id', $filters['account_ids']);
        }

        // Category filters
        if (isset($filters['category_ids'])) {
            $query->whereIn('transaction_category_id', $filters['category_ids']);
        }

        // Type filters
        if (isset($filters['type']) && $filters['type'] !== 'both') {
            $query->where('type', $filters['type']);
        }

        // Date filters
        if (isset($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        // Amount filters
        if (isset($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }
        if (isset($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
        }

        // Tag filters
        if (isset($filters['tag_ids'])) {
            $query->whereHas('tags', function ($tagQuery) use ($filters) {
                $tagQuery->whereIn('tags.id', $filters['tag_ids']);
            });
        }

        // Search filter
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('note', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($catQuery) use ($search) {
                        $catQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('account', function ($accQuery) use ($search) {
                        $accQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // User filter (security)
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
    }
}
