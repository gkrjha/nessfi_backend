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
        $userId = auth()->id();

        $responses = Response::with(['question.section', 'option'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $submissions = $responses->groupBy(function ($response) {
            return $response->created_at->format('Y-m');
        })->map(function ($monthResponses, $monthKey) use ($userId) {
            $firstResponse = $monthResponses->first();
            return [
                'id' => $monthKey,
                'user_id' => $userId,
                'submitted_at' => $firstResponse->created_at->toISOString(),
                'month' => $firstResponse->created_at->format('F Y'),
                'responses_count' => $monthResponses->count(),
                'responses' => ResponseResource::collection($monthResponses),
            ];
        })->values();

        return $this->successResponse(
            $submissions,
            'Submissions retrieved successfully'
        );
    }

    public function store(StoreResponseRequest $request)
    {
        try {
            DB::beginTransaction();

            $userId = auth()->id();

            $hasSubmittedThisMonth = Response::where('user_id', $userId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->exists();

            if ($hasSubmittedThisMonth) {
                return $this->errorResponse(
                    'You have already submitted an assessment this month. Please try again next month.',
                    403
                );
            }

            $allResponses = [];

            if ($request->has('responses')) {
                foreach ($request->responses as $responseData) {
                    $question = Question::findOrFail($responseData['question_id']);

                    if (!empty($responseData['selected_options'])) {
                        foreach ($responseData['selected_options'] as $optionId) {
                            $allResponses[] = Response::create([
                                'user_id' => $userId,
                                'question_id' => $responseData['question_id'],
                                'option_id' => $optionId,
                                'response_text' => $responseData['text_response'] ?? null,
                            ]);
                        }
                    } else {
                        $allResponses[] = Response::create([
                            'user_id' => $userId,
                            'question_id' => $responseData['question_id'],
                            'option_id' => null,
                            'response_text' => $responseData['text_response'] ?? null,
                        ]);
                    }
                }

                DB::commit();

                return $this->successResponse(
                    ResponseResource::collection($allResponses),
                    'Assessment submitted successfully',
                    201
                );
            }

            $question = Question::findOrFail($request->question_id);

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
            return $this->serverErrorResponse('Failed to submit response: ' . $e->getMessage());
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

    public function canSubmitThisMonth()
    {
        $userId = auth()->id();

        $hasSubmittedThisMonth = Response::where('user_id', $userId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->exists();

        $lastSubmission = Response::where('user_id', $userId)
            ->latest('created_at')
            ->first();

        return $this->successResponse([
            'can_submit' => !$hasSubmittedThisMonth,
            'has_submitted_this_month' => $hasSubmittedThisMonth,
            'last_submission_date' => $lastSubmission ? $lastSubmission->created_at->format('Y-m-d H:i:s') : null,
            'next_available_date' => $hasSubmittedThisMonth ? now()->addMonth()->startOfMonth()->format('Y-m-d') : null,
        ]);
    }

    // Admin methods
    public function getEmployees()
    {
        $employees = \App\Models\User::where('role', 'employee')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                // Count unique months where user submitted assessments
                $uniqueMonths = DB::table('responses')
                    ->where('user_id', $user->id)
                    ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
                    ->groupBy('month')
                    ->get()
                    ->count();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'assessmentCount' => $uniqueMonths,
                ];
            });

        return $this->successResponse(
            $employees,
            'Employees retrieved successfully'
        );
    }

    public function getCurrentMonthEmployees()
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        $employees = \App\Models\User::where('role', 'employee')
            ->whereHas('responses', function ($query) use ($currentYear, $currentMonth) {
                $query->whereYear('created_at', $currentYear)
                    ->whereMonth('created_at', $currentMonth);
            })
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                // Count unique months where user submitted assessments
                $uniqueMonths = DB::table('responses')
                    ->where('user_id', $user->id)
                    ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
                    ->groupBy('month')
                    ->get()
                    ->count();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'assessmentCount' => $uniqueMonths,
                ];
            });

        return $this->successResponse(
            $employees,
            'Current month employees retrieved successfully'
        );
    }

    public function getEmployeeResponses($userId)
    {
        $user = \App\Models\User::findOrFail($userId);

        if ($user->role !== 'employee') {
            return $this->errorResponse('User is not an employee', 400);
        }

        // Group responses by month for assessment submissions
        $responses = Response::with(['question.section', 'option'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $submissions = $responses->groupBy(function ($response) {
            return $response->created_at->format('Y-m');
        })->map(function ($monthResponses, $monthKey) use ($userId) {
            $firstResponse = $monthResponses->first();
            return [
                'id' => $monthKey,
                'user_id' => $userId,
                'submitted_at' => $firstResponse->created_at->toISOString(),
                'month' => $firstResponse->created_at->format('F Y'),
                'responses_count' => $monthResponses->count(),
                'responses' => ResponseResource::collection($monthResponses),
            ];
        })->values();

        return $this->successResponse(
            $submissions,
            'Employee submissions retrieved successfully'
        );
    }
}
