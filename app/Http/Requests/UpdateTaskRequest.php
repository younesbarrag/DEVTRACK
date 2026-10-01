<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Task;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $task = $this->route('task');
        $project = $this->route('project');

        if (! $this->user() || ! $task instanceof Task) {
            return false;
        }

        // Le lead gère la tâche, le développeur assigné ne peut que changer le statut.
        return $this->user()->isLead($project)
            || $this->user()->can('updateStatus', $task);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $project = $this->route('project');

        // Un développeur assigné ne peut modifier que le statut.
        if ($this->user() && $this->user()->isLead($project)) {
            return [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'deadline' => ['nullable', 'date_format:Y-m-d'],
                'priority' => ['required', 'in:low,medium,high'],
                'status' => ['nullable', 'in:todo,in_progress,done'],
                'assigned_to' => [
                    'required',
                    'integer',
                    Rule::exists('project_user', 'user_id')
                        ->where('project_id', $project->id),
                ],
            ];
        }

        return [
            'status' => ['required', 'in:todo,in_progress,done'],
        ];
    }
}
