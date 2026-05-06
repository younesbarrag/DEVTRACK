<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lead= User::where('email','lead@example.com')->first();
        $dev1= User::where('email','dev1@example.com')->first();
        $dev2= User::where('email','dev2@example.com')->first();

        $project1 = project::factory()->create([
            'user_id' => $lead->id,
            'title' => 'Project Alpha',
        ]);
        $project1->users()   ->attach([
            $lead->id=>['role'=>'lead'],
            $dev1->id =>['role'=>'developer'],
            $dev2->id =>['role'=>'developer'],
        ]);
        $project2 = project::factory()->create([
            'user_id' => $lead->id,
            'title' => 'Project Beta',
        ]);
        $project2->users()->attach([
            $lead->id=>['role'=>'lead'],
            $dev1->id =>['role'=>'developer'],
        ]);
        $project3 = project::factory()->create([
            'user_id' => $lead->id,
            'title' => 'Project Gamma',
        ]);
        $project3->users()->attach([
            $lead->id=>['role'=>'lead'],
            $dev2->id =>['role'=>'developer'],
        ]);

        project::factory(3)->create()->each(function($project){
            $users =User ::inRandomOrder()->limit(3)->get();
            $project->users()->attach($users->first()->id,['role'=>'lead']);
            foreach($users->skip(1) as $user){
                $project->users()->attach($user->id,['role'=>'developer']);
            }
        });

    }
  
}
