<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
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
            'name' => 'required|string|max:256',
            'duration_months' => 'required|integer|min:1',
            'benefits' => 'string',
            'amount' => 'integer|min:1000',
            'interval' => 'in:hourly,daily,weekly,monthly,quaterly,annually',
            'active' => 'in:true,false,1,0',
        ];
    }
}