<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    protected $token;
    protected $message;

    public function __construct($resource, $token = null, $message = null)
    {
        parent::__construct($resource);
        $this->token = $token;
        $this->message = $message;
    }

    public function toArray(Request $request): array
    {
        $data = [
            'success' => true,
            'message' => $this->message ?? 'Success',
            'data' => [
                'user' => new UserResource($this->resource),
            ]
        ];

        if ($this->token) {
            $data['data']['access_token'] = $this->token;
            $data['data']['token_type'] = 'Bearer';
        }

        return $data;
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode($this->resource->wasRecentlyCreated ?? false ? 201 : 200);
    }
}
