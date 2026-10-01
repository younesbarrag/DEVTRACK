<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Project;

class AddProjectMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        return $this->user() && $this->user()->can('manageMembers', $project);
    }

    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email'),
                $this->notAlreadyMember($project),
            ],
            'role' => ['nullable', 'in:lead,developer'],
        ];
    }

    /**
     * Refuse un utilisateur déjà rattaché au projet (comparaison par email,
     * car la valeur validée est l'email et non l'identifiant).
     */
    private function notAlreadyMember(Project $project): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($project): void {
            $alreadyMember = DB::table('project_user')
                ->join('users', 'users.id', '=', 'project_user.user_id')
                ->where('project_user.project_id', $project->id)
                ->where('users.email', $value)
                ->exists();

            if ($alreadyMember) {
                $fail('This user is already a member of the project.');
            }
        };
    }

    public function messages(): array
    {
        return [
            'email.exists' => 'No user found with that email address.',
            'role.in' => 'Role must be either lead or developer.',
        ];
    }
}
