<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'type' => Service::TYPE_CORE,
            'defined_by' => Service::DEFINED_BY_APPLICATION,
            'name' => 'ServD',
            'service_name' => 'servd',
            'version' => '8.0',
            'port' => '9000/8080/443',
            'enabled' => true,
            'has_volume' => false,
            'should_build' => true,
        ];
    }
}
