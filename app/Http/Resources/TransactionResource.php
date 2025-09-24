<?php

/**
 * TransactionResource
 *
 * API resource for formatting transaction data
 *
 * @author Fahed
 */

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Author: Fahed
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->formatCurrency($this->amount),
            'amount_raw' => (float) $this->amount,
            'commission_fee' => $this->formatCurrency($this->commission_fee),
            'commission_fee_raw' => (float) $this->commission_fee,
            'total_amount' => $this->formatCurrency($this->amount + $this->commission_fee),
            'total_amount_raw' => (float) ($this->amount + $this->commission_fee),
            'status' => $this->status,
            'status_label' => ucfirst($this->status),
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
                'email' => $this->sender->email,
            ],
            'receiver' => [
                'id' => $this->receiver->id,
                'name' => $this->receiver->name,
                'email' => $this->receiver->email,
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_at_human' => $this->created_at->diffForHumans(),
        ];
    }

    /**
     * Format currency with proper symbol and formatting.
     * Author: Fahed
     */
    private function formatCurrency(float $amount): string
    {
        return '$' . number_format($amount, 2);
    }
}
