<?php

namespace Tests\Unit\Models;

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

        $this->assertEquals(8, Service::count());
        $this->assertEquals('servd', Service::first()->service_name);
        $this->assertEquals('8.1', Service::first()->version);
    }

    /** @test */
    public function it_does_not_create_a_duplicate_core_service_when_version_has_changed(): void
    {
        $this->assertEquals(0, Service::count());

        Service::setupPredefinedServices();

        $service = Service::where('service_name', 'servd')->firstOrFail();

        $this->assertEquals('8.1', $service->version);

        $service->update(['version' => '8.2']);

        $this->assertEquals(1, Service::where('service_name', 'servd')->count());

        Service::setupPredefinedServices();

        $this->assertEquals(1, Service::where('service_name', 'servd')->count());
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

    /** @test */
    public function it_can_get_the_available_service_versions(): void
    {
        $this->assertEquals(
            [
                '5.7' => '5.7',
                '8.0' => '8.0',
            ],
            Service::getAvailableVersions('mysql')
        );
    }

    /** @test */
    public function it_can_get_the_expected_php_versions(): void
    {
        $this->assertEquals(
            [
                '7.4' => '7.4',
                '8.0' => '8.0',
                '8.1' => '8.1',
                '8.2' => '8.2',
                '8.3' => '8.3',
            ],
            Service::getPhpVersionChoices()
        );
    }

    /** @test */
    public function it_returns_latest_as_the_only_option_if_no_available_versions_defined(): void
    {
        $this->assertEquals(['latest'], Service::getAvailableVersions('redis'));
    }

    /** @test */
    public function get_available_versions_returns_null_when_given_invalid_service(): void
    {
        $this->assertNull(Service::getAvailableVersions('invalid-service'));
    }
}
