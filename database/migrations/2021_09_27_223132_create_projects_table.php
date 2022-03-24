<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('driver')->default('laravel');
            $table->string('location')->nullable();
            $table->string('directory_root')->nullable();
            $table->string('name')->nullable();
            $table->string('friendly_name')->nullable();
            $table->boolean('secure')->default(false);
            $table->timestamps();

            $table->index(['name', 'location']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
}
