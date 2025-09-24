<?php

/**
 * StoreTransactionRequest
 *
 * Form request for validating transaction creation
 *
 * @author Fahed
 */

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Author: Fahed
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * Author: Fahed
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'receiver_email' => 'required|email|exists:users,email',
            'amount' => 'required|numeric|min:0.01|max:100000',
            'commission_fee' => 'nullable|numeric|min:0|max:1000',
        ];
    }

    /**
     * Configure the validator instance.
     * Author: Fahed
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateBalanceSufficiency($validator);
            $this->validateReceiverNotSelf($validator);
        });
    }

    /**
     * Validate that user has sufficient balance for the transaction
     * Author: Fahed
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    protected function validateBalanceSufficiency($validator)
    {
        $user = Auth::user();
        $amount = $this->input('amount');
        $commissionFee = $this->input('commission_fee', 0);

        // Calculate default commission if not provided (1.5% of amount)
        if ($commissionFee === null || $commissionFee === 0) {
            $commissionFee = $amount * 0.015;
        }

        $totalRequired = $amount + $commissionFee;

        // Check if user has sufficient balance
        if ($user && $user->balance < $totalRequired) {
            if ($user->balance < $amount) {
                // If balance is less than the transfer amount
                $validator->errors()->add('amount',
                    "Insufficient balance. You have $" . number_format($user->balance, 2) .
                    " but trying to transfer $" . number_format($amount, 2)
                );
            } else {
                // If balance covers the amount but not the commission
                $validator->errors()->add('commission_fee',
                    "Low balance. You have $" . number_format($user->balance, 2) .
                    " but need $" . number_format($totalRequired, 2) .
                    " (amount + commission fee of $" . number_format($commissionFee, 2) . ")"
                );
            }
        }
    }

    /**
     * Validate that user is not trying to send money to themselves
     * Author: Fahed
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    protected function validateReceiverNotSelf($validator)
    {
        $user = Auth::user();
        $receiverEmail = $this->input('receiver_email');

        if ($user && $receiverEmail && $user->email === $receiverEmail) {
            $validator->errors()->add('receiver_email',
                'You cannot send money to yourself'
            );
        }
    }

    /**
     * Get custom error messages for validation rules.
     * Author: Fahed
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'receiver_email.required' => 'The receiver email field is required.',
            'receiver_email.email' => 'The receiver email must be a valid email address.',
            'receiver_email.exists' => 'The selected receiver email does not exist in our system.',
            'amount.required' => 'The transfer amount is required.',
            'amount.numeric' => 'The transfer amount must be a valid number.',
            'amount.min' => 'The transfer amount must be at least $0.01.',
            'amount.max' => 'The transfer amount may not be greater than $100,000.',
            'commission_fee.numeric' => 'The commission fee must be a valid number.',
            'commission_fee.min' => 'The commission fee cannot be negative.',
            'commission_fee.max' => 'The commission fee may not be greater than $1,000.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     * Author: Fahed
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'receiver_email' => 'receiver email',
            'amount' => 'transfer amount',
            'commission_fee' => 'commission fee',
        ];
    }
}
