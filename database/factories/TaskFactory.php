<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Project;
use App\Models\User;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{ 
   
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
      $status = fake()->randomElement(['todo', 'in_progress', 'done']);
        $deadline = fake()->dateTimeBetween('-1 week', '+2 weeks');

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(2),
            'status' => $status,
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'deadline' => $deadline,
            'project_id' => Project::factory(),
            'user_id' => User::factory(), // Le developer assigné
        ];
    }

    // Tâche urgente (deadline dans moins de 48h et pas done)
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'deadline' => fake()->dateTimeBetween('+1 hour', '+47 hours'),
            'status' => fake()->randomElement(['todo', 'in_progress']),
        ]);
    }

    // Tâche terminée
    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'done',
        ]);
    }
}
