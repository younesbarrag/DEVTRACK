<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
USE App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          $lead=User::create([
            'name' => 'Project Lead',
            'email' => 'lead@example.com',
            'password' => bcrypt('password'),
        ]);

        $dev1=User::create([
            'name' => 'Developer One',
            'email' => 'dev1@example.com',
            'password' => bcrypt('password'),
        ]);

        $dev2=User::create([
            'name' => 'Developer Two',
            'email' => 'dev2@example.com',
            'password' => bcrypt('password'),
        ]);

        User::factory(10)->create();
    }
}
