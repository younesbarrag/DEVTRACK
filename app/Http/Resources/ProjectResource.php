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
            
            'deadline' => $this->deadline ? $this->deadline : 'No deadline set',
            'created_at' => $this->created_at->format('Y-m-d'),

            'status' => $this->deleted_at ? 'Archived' : 'Active',

            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),

            'tasks_count' => $this->whenCounted('tasks'),
        ];
    }
}
