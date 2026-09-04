<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Boot-time sentinel: the entrypoint checks for a `provisioned_at` row here before
     * running migrate+seed, so container restarts never reseed over an attendee's work.
     */
    public function up(): void
    {
        Schema::create('system_state', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_state');
    }
};
