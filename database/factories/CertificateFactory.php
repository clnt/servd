<?php

namespace Database\Factories;

use App\Models\Certificate;
use Illuminate\Database\Eloquent\Factories\Factory;

class CertificateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Certificate::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'project_id' => 1,
            'common_name' => 'example-project.test',
            'container_path' => '/etc/nginx/ssl/example-project.test.crt',
            'path' => 'tests/Fake/UserHomeDirectory/.servd/certificates/example-project.test.crt',
            'expires' => now()->addDays(1825),
        ];
    }
}
