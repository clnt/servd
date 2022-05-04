<?php

use App\Models\Service;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->enum('type', [
                Service::TYPE_CORE,
                Service::TYPE_DATABASE,
                Service::TYPE_MEMORY_STORE,
                Service::TYPE_OTHER,
            ])->default(Service::TYPE_OTHER);
            $table->enum('defined_by', [
                Service::DEFINED_BY_APPLICATION,
                Service::DEFINED_BY_USER,
            ]);
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('service_name');
            $table->string('version');
            $table->string('port');
            $table->boolean('has_volume')->default(true);
            $table->boolean('should_build')->default(false);
            $table->text('service_folders')->nullable();
            $table->boolean('single_stub')->default(false);
            $table->text('available_versions')->nullable();
            $table->timestamps();

            $table->index(['enabled', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
}
