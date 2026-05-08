<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{    
//Determine if the user is authorized to make this request.
  public function authorize(): bool{
      return true;
    }
//Get the validation rules that apply to the request.
  public function rules(): array{
      return ['title'      => 'required|string|max:255','project_id' => 'required|exists:projects,id','status'     => 'sometimes|in:todo,doing,done',];}

  public function messages(): array{
      return ['title.required'      => 'Please enter a title for the task.','project_id.exists'   => 'The selected project does not exist.',];}
}