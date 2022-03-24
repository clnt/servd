<?php

namespace Tests\Unit;

use App\NginxComposer;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class NginxComposerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_instantiate_the_nginx_composer_with_static_make_method(): void
    {
        $this->assertInstanceOf(NginxComposer::class, NginxComposer::make());
    }
}
