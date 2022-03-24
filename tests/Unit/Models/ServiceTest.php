<?php

namespace Unit\Models;

use App\Models\Service;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use DatabaseMigrations;

    /** @test  */
    public function it_can_get_all_service_types(): void
    {
        $this->assertEquals([
            Service::TYPE_DATABASE,
            Service::TYPE_MEMORY_STORE,
            Service::TYPE_OTHER,
        ], Service::getServiceTypes());
    }

    /** @test */
    public function it_can_check_if_a_service_is_enabled(): void
    {
        $service = Service::factory()->create();

        $this->assertTrue($service->isEnabled());
    }

    /** @test */
    public function it_can_get_a_service_by_type(): void
    {
        Service::factory()->create();
        Service::factory()->create([
            'service_name' => 'test',
            'type' => Service::TYPE_MEMORY_STORE,
        ]);

        $this->assertEquals(2, Service::count());

        $result = Service::byType(Service::TYPE_MEMORY_STORE)->get();

        $this->assertEquals(1, $result->count());
        $this->assertEquals('test', $result->first()->service_name);
    }

    /** @test */
    public function it_can_setup_predefined_services(): void
    {
        $this->assertEquals(0, Service::count());

        Service::setupPredefinedServices();

        $this->assertEquals(10, Service::count());
        $this->assertEquals('servd', Service::first()->service_name);
        $this->assertEquals('8.0', Service::first()->version);
    }

    /** @test */
    public function it_can_get_enabled_services(): void
    {
        Service::factory()->create([
            'enabled' => true,
        ]);
        Service::factory()->create([
            'service_name' => 'test',
            'type' => Service::TYPE_MEMORY_STORE,
            'enabled' => false,
        ]);

        $this->assertEquals(1, Service::enabled()->count());
    }
}
