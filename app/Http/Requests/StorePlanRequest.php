<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'string|max:256',
            'duration_months' => 'integer|min:1',
            'benefits' => 'nullable|string',
            'amount' => 'required|integer|min:1000',
            'interval' => 'required|in:hourly,daily,weekly,monthly,quaterly,annually',
            'active' => 'nullable|in:true,false,1,0',
        ];
    }
}