<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'native_language' => ['nullable', 'string', 'max:255'],
            'target_language' => ['nullable', 'string', 'max:255'],
            'proficiency_level' => ['nullable', 'string', Rule::in(['A1', 'A2', 'B1', 'B2', 'C1', 'C2'])],
            'auto_update_proficiency' => ['nullable', 'boolean'],
            'localize_insights' => ['nullable', 'boolean'],
        ];
    }
}
