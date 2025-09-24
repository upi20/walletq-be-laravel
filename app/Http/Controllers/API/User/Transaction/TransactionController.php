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
        $query = $request->user()->transactions()
            ->with(['category', 'account', 'tags']);        // Exclude initial balance transactions
        $query->where('flag', Transaction::FLAG_NORMAL);

        // Apply filters if any
        if ($request->has('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->has('category_id')) {
            $query->where('transaction_category_id', $request->category_id);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Date range filtering
        if ($request->has('start_date')) {
            $query->whereDate('date', '>=', date('Y-m-d', strtotime($request->start_date)));
        }
        if ($request->has('end_date')) {
            $query->whereDate('date', '<=', date('Y-m-d', strtotime($request->end_date)));
        }

        // Apply sorting
        $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        $transactions = $query->get();

        return response()->json([
            'status' => 200,
            'message' => 'Transactions fetched successfully',
            'data' => $transactions
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
}
