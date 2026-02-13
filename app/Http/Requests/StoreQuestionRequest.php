<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    protected function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Only admin users can create questions.'
        );
    }

    public function rules(): array
    {
        return [
            'question' => 'required|string',
            'section_id' => 'required|exists:sections,id',
            'question_type' => 'required|in:multiple_choice_single,checkbox_multiple,text_only,mcq_textarea,checkbox_textarea',
            'text_response' => 'required|in:no_text,optional,required',
            'options' => 'required_unless:question_type,text_only|array|min:1',
            'options.*.option_text' => 'required|string',
        ];
    }
}
