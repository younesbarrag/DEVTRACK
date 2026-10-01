<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
//Determine if the user is authorized to make this request.
  public function authorize(): bool{
      return $this->user()->can('create', $this->route('project'));
    }
//Get the validation rules that apply to the request.
  public function rules(): array{
    $project = $this->route('project');

    return [
        'title' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'deadline' => ['nullable', 'date_format:Y-m-d'],
        'priority' => ['required', 'in:low,medium,high'],
        'assigned_to' => [
            'required',
            'integer',
            Rule::exists('project_user', 'user_id')
                ->where('project_id', $project->id),
        ],
    ];
  }

  public function messages(): array{
      return 
      [
        'deadline.date_format' => 'The deadline must be a valid date.',
        'assigned_to.exists' => 'The selected assignee must be a member of this project.',
    ];
    }
}
