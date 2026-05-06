<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;


class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
    public function run(): void
    {
    $projects = Project::all();

        foreach ($projects as $project) {
            // Récupérer les developers du projet
            $developers = $project->users()->wherePivot('role', 'developer')->get();

            if ($developers->isEmpty()) continue;

            // Créer des tâches variées
            Task::factory(3)->create([
                'project_id' => $project->id,
                'assigned_to' => $developers->random()->id,
                'status' => 'todo',
            ]);

            Task::factory(2)->create([
                'project_id' => $project->id,
                'assigned_to' => $developers->random()->id,
                'status' => 'in_progress',
            ]);

            Task::factory(2)->create([
                'project_id' => $project->id,
                'assigned_to' => $developers->random()->id,
                'status' => 'done',
            ]);

            // Quelques tâches urgentes
            Task::factory(2)->urgent()->create([
                'project_id' => $project->id,
                'assigned_to' => $developers->random()->id,
            ]);
        }
    }
}
