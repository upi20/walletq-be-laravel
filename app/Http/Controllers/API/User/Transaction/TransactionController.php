<?php

namespace App\Http\Controllers\API\User\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->transactions()
            ->with(['category', 'account', 'tags'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

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
        if ($request->has('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

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

            $transaction = new Transaction();
            $transaction->user_id = $request->user()->id;
            $transaction->account_id = $request->account_id;
            $transaction->transaction_category_id = $request->transaction_category_id;
            $transaction->type = $request->type;
            $transaction->amount = $request->amount;
            $transaction->date = $request->date;
            $transaction->note = $request->note;
            $transaction->flag = Transaction::FLAG_NORMAL;
            $transaction->save();

            // Attach tags if any
            if ($request->has('tags')) {
                $transaction->tags()->attach($request->tags);
            }

            User::refreshBalance($request->user()->id);
            
            DB::commit();

            return response()->json([
                'status' => 201,
                'message' => 'Transaction created successfully',
                'data' => $transaction->load(['category', 'account', 'tags'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
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
            foreach ($request->transactions as $transactionData) {
                $transaction = new Transaction();
                $transaction->user_id = $request->user()->id;
                $transaction->account_id = $transactionData['account_id'];
                $transaction->transaction_category_id = $transactionData['transaction_category_id'];
                $transaction->type = $transactionData['type'];
                $transaction->amount = $transactionData['amount'];
                $transaction->date = $transactionData['date'];
                $transaction->note = $transactionData['note'] ?? null;
                $transaction->flag = Transaction::FLAG_NORMAL;
                $transaction->save();

                // Attach tags if any
                if (isset($transactionData['tags']) && is_array($transactionData['tags'])) {
                    $transaction->tags()->attach($transactionData['tags']);
                }

                $createdTransactions[] = $transaction;
            }

            User::refreshBalance($request->user()->id);
            
            DB::commit();

            // Load relationships for response
            $transactions = collect($createdTransactions)->map(function($transaction) {
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
