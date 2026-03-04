<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'work_id' => $this['work_id'],
            'type' => $this['type'],
            'message' => $this['message'],
            'items' => $this['items'],
        ];
    }
}
