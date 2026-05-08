<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResurce extends JsonResource
{
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'task_title' => $this->title,
            'current_status' => strtoupper($this->status),
            'project_info' => [
                'id' => $this->project_id,
                'name' => $this->project->title,
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i'),
        ];
    }
}



