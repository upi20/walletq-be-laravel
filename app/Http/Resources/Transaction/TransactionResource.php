<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'import_id' => $this->import_id,
            'user_id' => $this->user_id,
            'account_id' => $this->account_id,
            'transaction_category_id' => $this->transaction_category_id,
            
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'formatted_amount' => $this->getFormattedAmount(),
            'date' => $this->date?->format('Y-m-d'),
            'formatted_date' => $this->date?->format('d M Y'),
            'note' => $this->note,
            
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
            'flag' => $this->flag,
            'flag_label' => $this->getFlagLabel(),
            
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relations
            'account' => $this->whenLoaded('account', function () {
                return [
                    'id' => $this->account->id,
                    'name' => $this->account->name,
                    'category' => $this->whenLoaded('account.category', function () {
                        return [
                            'id' => $this->account->category->id,
                            'name' => $this->account->category->name,
                            'type' => $this->account->category->type,
                        ];
                    }),
                ];
            }),
            
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'type' => $this->category->type,
                ];
            }),
            
            'tags' => $this->whenLoaded('tags', function () {
                return $this->tags->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                    ];
                });
            }),
            
            'source' => $this->whenLoaded('source', function () {
                if ($this->source) {
                    return [
                        'id' => $this->source->id,
                        'type' => $this->source_type,
                        'data' => $this->getSourceData(),
                    ];
                }
                return null;
            }),
        ];
    }

    /**
     * Get formatted amount with currency
     */
    private function getFormattedAmount(): string
    {
        $amount = number_format($this->amount, 0, ',', '.');
        $prefix = $this->type === 'income' ? '+' : '-';
        return "{$prefix} Rp {$amount}";
    }

    /**
     * Get flag label in Indonesian
     */
    private function getFlagLabel(): string
    {
        return match ($this->flag) {
            'normal' => 'Normal',
            'transfer_in' => 'Transfer Masuk',
            'transfer_out' => 'Transfer Keluar',
            'debt_payment' => 'Pembayaran Hutang',
            'debt_collect' => 'Penagihan Piutang',
            'initial_balance' => 'Saldo Awal',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Get source data based on source type
     */
    private function getSourceData(): array
    {
        if (!$this->source) {
            return [];
        }

        return match ($this->source_type) {
            'App\\Models\\Transfer' => [
                'from_account_id' => $this->source->from_account_id,
                'to_account_id' => $this->source->to_account_id,
                'amount' => (float) $this->source->amount,
                'note' => $this->source->note,
            ],
            'App\\Models\\Debt' => [
                'contact_name' => $this->source->contact_name,
                'type' => $this->source->type,
                'total_amount' => (float) $this->source->total_amount,
                'paid_amount' => (float) $this->source->paid_amount,
                'status' => $this->source->status,
            ],
            default => [],
        };
    }
}
