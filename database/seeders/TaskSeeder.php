<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, Task};
use App\Enums\TaskStatus;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        for ($i = 0; $i < 5; $i++) {
            Task::create([
                'user_id' => $user->id,
                'title' => fake()->words(3, true),
                'description' => fake()->text,
                'status' => fake()->randomElement(array_column(TaskStatus::cases(), 'value'))
            ]);
        }
    }
}
