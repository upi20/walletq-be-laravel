<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TransactionListResource extends ResourceCollection
{
    /**
     * Additional data to be added to response
     */
    public $additional = [];

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => TransactionResource::collection($this->collection),
            'meta' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
                'path' => $this->path(),
                'links' => [
                    'first' => $this->url(1),
                    'last' => $this->url($this->lastPage()),
                    'prev' => $this->previousPageUrl(),
                    'next' => $this->nextPageUrl(),
                ],
            ],
        ];
    }

    /**
     * Add summary data to response
     */
    public function withSummary(array $summary): self
    {
        $this->additional['summary'] = $summary;
        return $this;
    }

    /**
     * Add quick stats to response
     */
    public function withQuickStats(array $quickStats): self
    {
        $this->additional['quick_stats'] = $quickStats;
        return $this;
    }

    /**
     * Add applied filters to response
     */
    public function withFilters(array $filters): self
    {
        $this->additional['filters'] = $filters;
        return $this;
    }

    /**
     * Add master data for filters to response
     */
    public function withMasterData(array $masterData): self
    {
        $this->additional['master_data'] = $masterData;
        return $this;
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return $this->additional;
    }
}
