<?php

/**
 * UpdateBeneficiaryRequest
 *
 * Form request for validating beneficiary updates
 *
 * @author Fahed
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBeneficiaryRequest extends FormRequest
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
            'nickname' => 'nickname',
            'notes' => 'notes',
            'is_favorite' => 'favorite status',
        ];
    }
}
