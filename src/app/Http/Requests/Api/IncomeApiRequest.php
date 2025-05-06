<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class IncomeApiRequest extends FormRequest
{

//    public function authorize()
//    {
//        return auth()->check();
//    }

    public function rules()
    {
        return [
            'dateFrom' => 'nullable|date',
            'dateTo' => 'nullable|date|after_or_equal:dateFrom',
            'limit' => 'integer|min:1|max:1000',
            'offset' => 'integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'dateFrom.date' => 'The start date must be a valid date.',
            'dateTo.date' => 'The end date must be a valid date.',
            'dateTo.after_or_equal' => 'The end date must be equal to or after the start date.',
            'limit.integer' => 'The limit must be an integer.',
            'limit.min' => 'The limit must be at least 1.',
            'limit.max' => 'The limit cannot exceed 1000.',
            'offset.integer' => 'The offset must be an integer.',
            'offset.min' => 'The offset cannot be negative.',
        ];
    }
}