<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;

class Transactions implements ToCollection
{
    private $import_id;
    public function __construct($import_id, $showProgress = false)
    {
        $this->import_id = $import_id;
    }

    public function collection(Collection $rows)
    {
        $user = Auth::user();
        if (is_null($user)) return true;
        $user_id = $user->id;

        $startFrom = 2;
        $isDebt = false;
        $debt_payment = 'Tagih Hutang';
        $debt_collect = ['Beri Pinjaman', 'Tambah Pinjaman'];
        $debits = array_merge($debt_collect, [$debt_payment]);

        $isTransfer = false;
        $transfer_in = 'Terima Saldo';
        $transfer_out = 'Kirim Saldo';
        $transfers = [$transfer_in, $transfer_out];
        $initial_balance = 'Saldo Awal';
        $transfer = (object)[];

        foreach ($rows as $k => $r) {
            if ($k < $startFrom - 1) continue;
            $kategori = $r[charIndex('A')];
            $rekening = $r[charIndex('B')];
            $jumlah = $r[charIndex('C')];
            $tanggal = $r[charIndex('D')];
            $catatan = $r[charIndex('E')];
            $type = $r[charIndex('F')] == '+' ? 'income' : 'expense';
            $isDebt = in_array($kategori, $debits);
            $isTransfer = in_array($kategori, $transfers);

            $account = Account::where('name', $rekening)->where('user_id', $user_id)->first();
            if (is_null($account)) {
                $account = new Account();
                $account->name = $rekening;
                $account->user_id = $user_id;
                $account->user_id = $user_id;
                $account->save();
            }

            $transaction_category = TransactionCategory::where('name', $kategori)->where('user_id', $user_id)->where('type', $type)->first();
            if (is_null($transaction_category)) {
                $transaction_category = new TransactionCategory();
                $transaction_category->name = $kategori;
                $transaction_category->user_id = $user_id;
                $transaction_category->type = $type;
                $transaction_category->save();
            }

            $transaction = new Transaction();
            $transaction->import_id = $this->import_id;
            $transaction->user_id = $user_id;
            $transaction->account_id = $account->id;
            $transaction->transaction_category_id = $transaction_category->id;
            $transaction->type = $type;
            $transaction->amount = $jumlah;
            $transaction->date = $tanggal;
            $transaction->note = $catatan;
            $transaction->save();

            // Transfer
            if ($isTransfer) {
                if ($kategori == $transfer_out) {
                    $transfer = new Transfer();
                    $transfer->user_id = $user_id;
                    $transfer->from_account_id = $account->id;
                    $transfer->to_account_id = $account->id;
                    $transfer->amount = $transaction->amount;
                    $transfer->date = $transaction->date;
                    $transfer->save();
                } else {
                    $transfer->to_account_id = $account->id;
                    $transfer->save();
                }

                // Flag Check
                $transaction->source_id = $transfer->id;
                $transaction->flag = $kategori == $transfer_in ? 'transfer_in' : 'transfer_out';
                $transaction->save();

                if ($transaction_category->is_hide == false) {
                    $transaction_category->is_hide = true;
                    $transaction_category->save();
                }
            }

            // Debt
            if ($isDebt) {

                // Flag Check
                $transaction->flag = in_array($kategori, $debt_collect) ? 'debt_collect' : 'debt_payment';
                $transaction->save();

                if ($transaction_category->is_hide == false) {
                    $transaction_category->is_hide = true;
                    $transaction_category->save();
                }
            }

            // Initial Balance
            if ($kategori == $initial_balance) {
                // Flag Check
                $transaction->flag = 'initial_balance';
                $transaction->save();

                $account->initial_balance = $transaction->amount;
                $account->save();

                if ($transaction_category->is_hide == false) {
                    $transaction_category->is_hide = true;
                    $transaction_category->save();
                }
            }
        }
    }
}
