<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'driver' => 'laravel',
            'location' => 'test/example-project',
            'name' => 'example-project',
            'friendly_name' => 'Example Project',
            'secure' => false,
        ];
    }
}
