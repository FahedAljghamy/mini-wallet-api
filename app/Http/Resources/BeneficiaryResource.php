<?php

/**
 * BeneficiaryResource
 *
 * API resource for formatting beneficiary data
 *
 * @author Fahed
 */

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeneficiaryResource extends JsonResource
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
            'nickname' => $this->nickname,
            'notes' => $this->notes,
            'is_favorite' => $this->is_favorite,
            'beneficiary' => [
                'id' => $this->beneficiaryUser->id,
                'name' => $this->beneficiaryUser->name,
                'email' => $this->beneficiaryUser->email,
                'balance' => $this->formatCurrency($this->beneficiaryUser->balance),
                'balance_raw' => (float) $this->beneficiaryUser->balance,
                'email_verified_at' => $this->beneficiaryUser->email_verified_at?->format('Y-m-d H:i:s'),
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
