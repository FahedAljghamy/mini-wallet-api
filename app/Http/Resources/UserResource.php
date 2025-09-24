<?php

/**
 * UserResource
 *
 * API resource for formatting user data
 *
 * @author Fahed
 */

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'balance' => $this->formatCurrency($this->balance),
            'balance_raw' => (float) $this->balance,
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
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
