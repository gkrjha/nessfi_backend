<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'section_id' => $this->section_id,
            'section' => new SectionResource($this->whenLoaded('section')),
            'question_type' => $this->question_type,
            'text_response' => $this->text_response,
            'options' => OptionResource::collection($this->whenLoaded('options')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
