<?php

namespace App\Http\Controllers\API\User\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    // Menampilkan semua akun untuk user
    public function index(Request $request)
    {
        $user = $request->user();
        $accounts = $user->accounts()->orderBy('name', 'asc')->with(['category'])->get();
        
        // Calculate total balance
        $totalBalance = $accounts->sum('current_balance');
        
        // Update user's balance
        $user->balance = $totalBalance;
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'Accounts fetched successfully',
            'data' => [
                'balance' => $totalBalance,
                'accounts' => $accounts
            ]
        ], 200);
    }

    // Menambahkan akun baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'account_category_id' => 'nullable|exists:account_categories,id',
            'initial_balance' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();

        // Create account first
        $account = $request->user()->accounts()->create([
            'name' => $request->name,
            'account_category_id' => $request->account_category_id,
            'initial_balance' => $request->initial_balance,
            'current_balance' => $request->initial_balance,
        ]);

        // Always create initial balance transaction if initial balance is not 0
        if ($request->initial_balance != 0) {
            $type = Transaction::TYPE_INCOME;
            $kategori = Transaction::CATEGORY_INITIAL_BALANCE;
            $transaction_category = TransactionCategory::where('name', $kategori)->where('user_id', $account->user_id)->where('type', $type)->first();
            if (is_null($transaction_category)) {
                $transaction_category = new TransactionCategory();
                $transaction_category->name = $kategori;
                $transaction_category->user_id = $account->user_id;
                $transaction_category->type = $type;
                $transaction_category->save();
            }

            $transaction = new Transaction();
            $transaction->user_id = $account->user_id;
            $transaction->account_id = $account->id;
            $transaction->transaction_category_id = $transaction_category->id;
            $transaction->type = $type;
            $transaction->amount = $request->initial_balance;
            $transaction->date = now();
            $transaction->flag = Transaction::FLAG_INITIAL_BALANCE;
            $transaction->save();
        }

        User::refreshBalance($account->user_id);
        DB::commit();
        return response()->json([
            'status' => 201,
            'message' => 'Account created successfully',
            'data' => $account
        ], 201);
    }

    // Menampilkan akun tertentu
    public function show(Request $request, $id)
    {
        $account = $request->user()->accounts()->with(['category'])->find($id);

        if (!$account) {
            return response()->json([
                'status' => 404,
                'message' => 'Account not found'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Account fetched successfully',
            'data' => $account
        ], 200);
    }

    // Mengupdate akun
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'account_category_id' => 'nullable|exists:account_categories,id',
            'initial_balance' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $account = $request->user()->accounts()->find($id);

        if (!$account) {
            return response()->json([
                'status' => 404,
                'message' => 'Account not found'
            ], 404);
        }

        // Mengupdate akun
        $updateData = [
            'name' => $request->name
        ];

        if ($request->has('account_category_id')) {
            $updateData['account_category_id'] = $request->account_category_id;
        }

        if ($request->has('initial_balance')) {
            $updateData['initial_balance'] = $request->initial_balance;
            $updateData['current_balance'] = $request->initial_balance;
        }

        $account->update($updateData);

        return response()->json([
            'status' => 200,
            'message' => 'Account updated successfully',
            'data' => $account
        ], 200);
    }

    // Menghapus akun
    public function destroy(Request $request, $id)
    {
        $account = $request->user()->accounts()->find($id);

        if (!$account) {
            return response()->json([
                'status' => 404,
                'message' => 'Account not found'
            ], 404);
        }

        // Check if account has any transactions
        if ($account->transactions()->exists()) {
            return response()->json([
                'status' => 422,
                'message' => 'Cannot delete account with transactions'
            ], 422);
        }

        // Menghapus akun
        $account->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Account deleted successfully'
        ], 200);
    }
}
