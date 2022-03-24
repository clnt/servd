<?php

namespace Unit\Models;

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use DatabaseMigrations;

    public Setting $setting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setting = Setting::factory()->create();
    }

    /** @test */
    public function it_can_get_the_setting_value(): void
    {
        $this->assertEquals($this->setting->value, Setting::get(Setting::KEY_PHP_VERSION));
    }

    /** @test */
    public function find_accepts_a_key_and_returns_setting_model(): void
    {
        $result = Setting::find($this->setting->key);

        $this->assertSame($this->setting->key, $result->key);
        $this->assertSame($this->setting->value, $result->value);
    }

    /** @test */
    public function it_can_update_a_value_by_key(): void
    {
        $this->assertEquals('8.1', $this->setting->value);

        $this->setting = Setting::updateValueByKey(Setting::KEY_PHP_VERSION, '8.0');

        $this->assertEquals('8.0', $this->setting->value);
    }
}
