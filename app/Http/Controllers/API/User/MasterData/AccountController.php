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
        $data = [
            'balance' => (float)$user->balance,
            'accounts' => $accounts,
        ];
        return response()->json([
            'status' => 200,
            'message' => 'Accounts fetched successfully',
            'data' => $data
        ]);
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

        // Menyimpan akun baru untuk user
        $account = $request->user()->accounts()->create([
            'name' => $request->name,
            'account_category_id' => $request->account_category_id,
            'initial_balance' => $request->initial_balance,
            'current_balance' => $request->initial_balance,  // Initial balance is also the current balance initially
        ]);

        if ($request->initial_balance > 0) {
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
        ]);
    }

    // Menampilkan akun tertentu
    public function show(Request $request, $id)
    {
        try {
            $account = $request->user()->accounts()->with(['category'])->find($id);
            return response()->json([
                'status' => 200,
                'message' => 'Account fetched successfully',
                'data' => $account
            ]);
        } catch (\Throwable $th) {
            Log::info('Error Account Controller Show');
            Log::info($th);
            return response()->json([
                'status' => 404,
                'message' => 'Account not found'
            ], 404);
        }
    }

    // Mengupdate akun
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'account_category_id' => 'required|exists:account_categories,id',
            'initial_balance' => 'required|numeric',
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
        $account->update([
            'name' => $request->name,
            'account_category_id' => $request->account_category_id,
            'initial_balance' => $request->initial_balance,
            'current_balance' => $request->initial_balance,  // update current balance if needed
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Account updated successfully',
            'data' => $account
        ]);
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

        // Menghapus akun
        $account->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Account deleted successfully'
        ]);
    }
}
