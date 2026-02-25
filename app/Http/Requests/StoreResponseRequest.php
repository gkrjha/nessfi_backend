<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Question;

class StoreResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'employee';
    }

    public function rules(): array
    {
        if ($this->has('responses')) {
            return [
                'responses' => 'required|array|min:1',
                'responses.*.question_id' => 'required|exists:questions,id',
                'responses.*.selected_options' => 'nullable|array',
                'responses.*.selected_options.*' => 'exists:options,id',
                'responses.*.text_response' => 'nullable|string',
            ];
        }

        $question = Question::find($this->question_id);

        $rules = [
            'question_id' => 'required|exists:questions,id',
        ];

        if ($question) {
            if (in_array($question->question_type, ['multiple_choice_single', 'mcq_textarea'])) {
                $rules['option_id'] = 'required|exists:options,id';
            } elseif (in_array($question->question_type, ['checkbox_multiple', 'checkbox_textarea'])) {
                $rules['option_ids'] = 'required|array|min:1';
                $rules['option_ids.*'] = 'exists:options,id';
            }

            if ($question->text_response === 'required' || $question->question_type === 'text_only') {
                $rules['response_text'] = 'required|string';
            } elseif ($question->text_response === 'optional') {
                $rules['response_text'] = 'nullable|string';
            }
        }

        return $rules;
    }
}
