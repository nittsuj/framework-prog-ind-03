<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk menghasilkan data dummy project yang realistis.
 *
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $themes = ['Ollama', 'LangChain', 'AutoGen', 'CrewAI'];

        return [
            'judul' => '[Placeholder] '.fake()->sentence(4),
            'deskripsi' => fake()->paragraph(3),
            'tema_agent' => fake()->randomElement($themes),
            'api_key_secure' => 'sk-'.fake()->bothify('????????????????????????'),
        ];
    }
}
