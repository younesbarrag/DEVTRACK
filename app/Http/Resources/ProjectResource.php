<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'deadline' => $this->deadline,
           'is_archived' => $this->deleted_at !== null ,
           'tasssk_count' => $this->whenCounted('tasks'),

           'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
           'user'=> UserResource::collection($this->whenLoaded('users')),

           ' created_at' => $this->created_at ->format('Y-m-d H:i:s'),
           'updated_at' => $this->updated_at ->format('Y-m-d H:i:s'),
        ];
        
    }
}
