<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResponseRequest;
use App\Http\Resources\ResponseResource;
use App\Models\Response;
use App\Models\Question;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class ResponseController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $responses = Response::with(['user', 'question', 'option'])
            ->where('user_id', auth()->id())
            ->get();

        return $this->successResponse(
            ResponseResource::collection($responses),
            'Responses retrieved successfully'
        );
    }

    public function store(StoreResponseRequest $request)
    {
        try {
            DB::beginTransaction();

            $question = Question::findOrFail($request->question_id);
            $userId = auth()->id();

            // Handle checkbox multiple - multiple responses
            if (in_array($question->question_type, ['checkbox_multiple', 'checkbox_textarea'])) {
                $responses = [];
                foreach ($request->option_ids as $optionId) {
                    $responses[] = Response::create([
                        'user_id' => $userId,
                        'question_id' => $request->question_id,
                        'option_id' => $optionId,
                        'response_text' => $request->response_text ?? null,
                    ]);
                }

                DB::commit();

                return $this->successResponse(
                    ResponseResource::collection($responses),
                    'Responses submitted successfully',
                    201
                );
            }

            // Handle single response (MCQ, text, etc.)
            $response = Response::create([
                'user_id' => $userId,
                'question_id' => $request->question_id,
                'option_id' => $request->option_id ?? null,
                'response_text' => $request->response_text ?? null,
            ]);

            DB::commit();

            return $this->successResponse(
                new ResponseResource($response),
                'Response submitted successfully',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('Failed to submit response');
        }
    }

    public function show(Response $response)
    {
        if ($response->user_id !== auth()->id()) {
            return $this->forbiddenResponse('You can only view your own responses');
        }

        $response->load(['user', 'question', 'option']);
        return $this->successResponse(
            new ResponseResource($response),
            'Response retrieved successfully'
        );
    }

    public function destroy(Response $response)
    {
        if ($response->user_id !== auth()->id()) {
            return $this->forbiddenResponse('You can only delete your own responses');
        }

        $response->delete();
        return $this->successResponse(
            null,
            'Response deleted successfully'
        );
    }

    // Admin methods
    public function getEmployees()
    {
        $employees = \App\Models\User::where('role', 'employee')
            ->withCount('responses')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'assessmentCount' => $user->responses_count,
                ];
            });

        return $this->successResponse(
            $employees,
            'Employees retrieved successfully'
        );
    }

    public function getEmployeeResponses($userId)
    {
        $user = \App\Models\User::findOrFail($userId);

        if ($user->role !== 'employee') {
            return $this->errorResponse('User is not an employee', 400);
        }

        $responses = Response::with(['question.section', 'option'])
            ->where('user_id', $userId)
            ->get();

        return $this->successResponse(
            ResponseResource::collection($responses),
            'Employee responses retrieved successfully'
        );
    }
}
