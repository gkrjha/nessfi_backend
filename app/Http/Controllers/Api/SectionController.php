<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SectionResource;
use App\Models\Section;
use App\Traits\ApiResponse;

class SectionController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $sections = Section::with('question')->get();
        return $this->successResponse(
            SectionResource::collection($sections),
            'Sections retrieved successfully'
        );
    }
}
