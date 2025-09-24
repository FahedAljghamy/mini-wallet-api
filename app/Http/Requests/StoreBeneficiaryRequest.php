<?php

/**
 * StoreBeneficiaryRequest
 *
 * Form request for validating beneficiary creation
 *
 * @author Fahed
 */

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreBeneficiaryRequest extends FormRequest
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
            'beneficiary_email' => [
                'required',
                'email',
                'exists:users,email',
                Rule::notIn([Auth::user()->email ?? '']), // Cannot add yourself
            ],
            'nickname' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
            'is_favorite' => 'nullable|boolean',
        ];
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
            'beneficiary_email.required' => 'The beneficiary email field is required.',
            'beneficiary_email.email' => 'The beneficiary email must be a valid email address.',
            'beneficiary_email.exists' => 'The selected beneficiary email does not exist in our system.',
            'beneficiary_email.not_in' => 'You cannot add yourself as a beneficiary.',
            'nickname.max' => 'The nickname may not be greater than 50 characters.',
            'notes.max' => 'The notes may not be greater than 500 characters.',
            'is_favorite.boolean' => 'The favorite field must be true or false.',
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
            'beneficiary_email' => 'beneficiary email',
            'nickname' => 'nickname',
            'notes' => 'notes',
            'is_favorite' => 'favorite status',
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
            $this->validateUniqueBeneficiary($validator);
        });
    }

    /**
     * Validate that beneficiary is not already added
     * Author: Fahed
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    protected function validateUniqueBeneficiary($validator)
    {
        $user = Auth::user();
        $beneficiaryEmail = $this->input('beneficiary_email');

        if ($user && $beneficiaryEmail) {
            $beneficiaryUser = User::where('email', $beneficiaryEmail)->first();

            if ($beneficiaryUser) {
                $existingBeneficiary = $user->beneficiaries()
                    ->where('beneficiary_user_id', $beneficiaryUser->id)
                    ->exists();

                if ($existingBeneficiary) {
                    $validator->errors()->add('beneficiary_email',
                        'This user is already in your beneficiaries list'
                    );
                }
            }
        }
    }
}
