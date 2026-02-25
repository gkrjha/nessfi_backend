<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'question' => 'sometimes|required|string',
            'section_id' => 'sometimes|required|exists:sections,id',
            'question_type' => 'sometimes|required|in:multiple_choice_single,checkbox_multiple,text_only,mcq_textarea,checkbox_textarea',
            'text_response' => 'sometimes|required|in:no_text,optional,required',
            'options' => 'sometimes|array|min:1',
            'options.*.option_text' => 'required|string',
        ];
    }
}
