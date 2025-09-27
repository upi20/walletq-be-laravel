<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Transaction;

class FilterTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Date filters
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'date_preset' => 'nullable|in:today,week,month,year,custom',
            
            // Account & Category filters
            'account_ids' => 'nullable|array',
            'account_ids.*' => 'integer|exists:accounts,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:transaction_categories,id',
            
            // Type & Flag filters
            'type' => 'nullable|in:income,expense,both',
            'flags' => 'nullable|array',
            'flags.*' => 'string|in:' . implode(',', Transaction::FLAGS),
            
            // Amount filters
            'amount_min' => 'nullable|numeric|min:0',
            'amount_max' => 'nullable|numeric|min:0|gte:amount_min',
            
            // Tag & Search filters
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id',
            'search' => 'nullable|string|max:255',
            
            // Pagination & Sorting
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:10|max:100',
            'sort_by' => 'nullable|in:date,amount,account,category',
            'sort_order' => 'nullable|in:asc,desc',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'date_to.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal mulai.',
            'amount_max.gte' => 'Jumlah maksimal harus lebih besar atau sama dengan jumlah minimal.',
            'account_ids.*.exists' => 'Akun yang dipilih tidak valid.',
            'category_ids.*.exists' => 'Kategori yang dipilih tidak valid.',
            'tag_ids.*.exists' => 'Tag yang dipilih tidak valid.',
            'per_page.max' => 'Maksimal 100 data per halaman.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert string arrays to arrays if needed
        if ($this->has('account_ids') && is_string($this->input('account_ids'))) {
            $this->merge([
                'account_ids' => explode(',', $this->input('account_ids'))
            ]);
        }

        if ($this->has('category_ids') && is_string($this->input('category_ids'))) {
            $this->merge([
                'category_ids' => explode(',', $this->input('category_ids'))
            ]);
        }

        if ($this->has('tag_ids') && is_string($this->input('tag_ids'))) {
            $this->merge([
                'tag_ids' => explode(',', $this->input('tag_ids'))
            ]);
        }

        if ($this->has('flags') && is_string($this->input('flags'))) {
            $this->merge([
                'flags' => explode(',', $this->input('flags'))
            ]);
        }

        // Set default values
        $this->merge([
            'type' => $this->input('type') ?? 'both',
            'sort_by' => $this->input('sort_by') ?? 'date',
            'sort_order' => $this->input('sort_order') ?? 'desc',
            'per_page' => $this->input('per_page') ?? 25,
        ]);
    }

    /**
     * Get validated data with user context
     */
    public function getValidatedFilters(): array
    {
        $validated = $this->validated();
        
        // Add user_id for security
        $validated['user_id'] = $this->user()->id;
        
        // Remove empty arrays and null values
        return array_filter($validated, function ($value) {
            if (is_array($value)) {
                return !empty($value);
            }
            return $value !== null && $value !== '';
        });
    }
}
