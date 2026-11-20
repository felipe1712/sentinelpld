<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sentinel_system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 64)->unique();
            $table->text('valor')->nullable();
            $table->boolean('cifrado')->default(false);
            $table->string('grupo', 32)->default('general');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sentinel_system_settings');
    }
};
