<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $questions = Question::with(['section', 'options'])->get();
        return $this->successResponse(
            QuestionResource::collection($questions),
            'Questions retrieved successfully'
        );
    }

    public function store(StoreQuestionRequest $request)
    {

        try {
            DB::beginTransaction();

            $question = Question::create($request->only([
                'question',
                'section_id',
                'question_type',
                'text_response'
            ]));

            if ($request->has('options') && $request->question_type !== 'text_only') {
                foreach ($request->options as $option) {
                    $question->options()->create([
                        'option_text' => $option['option_text']
                    ]);
                }
            }

            $question->load(['section', 'options']);

            DB::commit();

            return $this->successResponse(
                new QuestionResource($question),
                'Question created successfully',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('Failed to create question: ' . $e->getMessage());
        }
    }

    public function show(Question $question)
    {
        $question->load(['section', 'options']);
        return $this->successResponse(
            new QuestionResource($question),
            'Question retrieved successfully'
        );
    }

    public function update(UpdateQuestionRequest $request, Question $question)
    {
        try {
            DB::beginTransaction();

            $question->update($request->only([
                'question',
                'section_id',
                'question_type',
                'text_response'
            ]));

            if ($request->has('options')) {
                $question->options()->delete();
                foreach ($request->options as $option) {
                    $question->options()->create([
                        'option_text' => $option['option_text']
                    ]);
                }
            }

            $question->load(['section', 'options']);

            DB::commit();

            return $this->successResponse(
                new QuestionResource($question),
                'Question updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('Failed to update question: ' . $e->getMessage());
        }
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return $this->successResponse(
            null,
            'Question deleted successfully'
        );
    }
}
