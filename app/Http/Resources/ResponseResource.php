<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'question_id' => $this->question_id,
            'question' => new QuestionResource($this->whenLoaded('question')),
            'option_id' => $this->option_id,
            'option' => new OptionResource($this->whenLoaded('option')),
            'response_text' => $this->response_text,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
