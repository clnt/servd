<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificatesTable extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', static function (Blueprint $table): void {
            $table->id();
            $table->bigInteger('project_id');
            $table->string('common_name');
            $table->string('container_path');
            $table->string('path');
            $table->timestamp('expires');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
}
