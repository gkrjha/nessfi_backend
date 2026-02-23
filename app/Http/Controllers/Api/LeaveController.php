<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $leaves = Leave::with(['user', 'approver'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $leaves = Leave::with(['approver'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return $this->successResponse($leaves, 'Leaves retrieved successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'subject' => 'required|string|max:255',
            'reason' => 'required|string',
        ]);

        $leave = Leave::create([
            'user_id' => auth()->id(),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'subject' => $request->subject,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        $leave->load(['user', 'approver']);

        return $this->successResponse($leave, 'Leave request submitted successfully', 201);
    }

    public function update(Request $request, Leave $leave)
    {
        if ($leave->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return $this->forbiddenResponse('You can only update your own leave requests');
        }

        if ($leave->status !== 'pending') {
            return $this->errorResponse('Cannot update leave request that has been processed', 400);
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'subject' => 'required|string|max:255',
            'reason' => 'required|string',
        ]);

        $leave->update($request->only(['start_date', 'end_date', 'subject', 'reason']));
        $leave->load(['user', 'approver']);

        return $this->successResponse($leave, 'Leave request updated successfully');
    }

    public function destroy(Leave $leave)
    {
        if ($leave->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return $this->forbiddenResponse('You can only delete your own leave requests');
        }

        if ($leave->status !== 'pending') {
            return $this->errorResponse('Cannot delete leave request that has been processed', 400);
        }

        $leave->delete();

        return $this->successResponse(null, 'Leave request deleted successfully');
    }

    public function approve(Request $request, Leave $leave)
    {
        if (auth()->user()->role !== 'admin') {
            return $this->forbiddenResponse('Only admins can approve leave requests');
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string',
        ]);

        $leave->update([
            'status' => $request->status,
            'approved_by' => auth()->id(),
            'admin_notes' => $request->admin_notes,
        ]);

        $leave->load(['user', 'approver']);

        return $this->successResponse($leave, 'Leave request ' . $request->status . ' successfully');
    }

    public function getByDateRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $user = auth()->user();

        $query = Leave::with(['user', 'approver'])
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($q2) use ($request) {
                        $q2->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            });

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $leaves = $query->get();

        return $this->successResponse($leaves, 'Leaves retrieved successfully');
    }
}
