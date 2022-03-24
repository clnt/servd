<?php

namespace Tests\Unit\Models;

use App\Models\Certificate;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_check_if_the_certificate_file_exists(): void
    {
        $certificate = Certificate::factory()->create();

        $this->assertTrue($certificate->fileExists());
    }
}
