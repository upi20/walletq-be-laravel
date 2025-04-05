<?php

namespace App\Http\Controllers\API\User\Transaction;

use App\Http\Controllers\Controller;
use App\Imports\Transactions;
use App\Models\Account;
use App\Models\ImportTransaction;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ImportTransactionController extends Controller
{
    public function handleImport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = Auth::user();
        ini_set('max_execution_time', 900);
        $file = $request->file('file');
        $fileName = date('YmdHis-') . $this->clearName($file->getClientOriginalName());
        $path = 'import/excel/transaction';
        $path = $file->storeAs($path, $fileName, 'public');

        $error_message = null;
        try {
            DB::beginTransaction();

            // simpan data upload ke database
            $import = new ImportTransaction();
            $import->file = $path;
            $import->save();

            Excel::import(new Transactions($import->id), $file);

            $user->balance = $this->refreshBalance($user->id);
            $user->save();
            DB::commit();
        } catch (\Exception $ex) {
            $error_message = $ex->getMessage();
        }
        $statusCode = $error_message ? 400 : 200;
        $transactions = Transaction::with(['category', 'account'])
            ->where('user_id', $user->id)
            ->where('import_id', $import->id)->get();
        return response()->json([
            'status' => $statusCode,
            'message' => $error_message ? $error_message : 'Import Success',
            'data' => $transactions
        ], $statusCode);
    }

    private function clearName($original)
    {
        // Pisahkan nama dan ekstensi
        $info = pathinfo($original);

        // Bersihkan nama file: hapus karakter ilegal & ganti spasi jadi "-"
        $cleanName = preg_replace('/[\\\\\/:*?"<>|]/', '', $info['filename']);
        $cleanName = preg_replace('/\s+/', '-', $cleanName);

        // Gabungkan kembali dengan ekstensi
        $cleanFile = $cleanName . '.' . $info['extension'];

        return strtolower($cleanFile);
    }

    private function refreshBalance($user_id)
    {
        $amount = 0;
        $allAccount = Account::where('user_id', $user_id)->get();
        foreach ($allAccount as $account) {
            $income = Transaction::where('user_id', $user_id)
                ->where('account_id', $account->id)
                ->where('type', 'income')->sum('amount');
            $expense = Transaction::where('user_id', $user_id)
                ->where('account_id', $account->id)
                ->where('type', 'expense')->sum('amount');

            $account->current_balance = $income - $expense;
            $account->save();

            $amount+=$account->current_balance;
        }

        return $amount;
    }
}
