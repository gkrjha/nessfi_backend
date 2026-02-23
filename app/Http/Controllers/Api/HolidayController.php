<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);

        $holidays = Holiday::whereYear('date', $year)
            ->orderBy('date')
            ->get();

        return $this->successResponse($holidays, 'Holidays retrieved successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $holiday = Holiday::create($request->all());

        return $this->successResponse($holiday, 'Holiday created successfully', 201);
    }

    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $holiday->update($request->all());

        return $this->successResponse($holiday, 'Holiday updated successfully');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return $this->successResponse(null, 'Holiday deleted successfully');
    }
}
