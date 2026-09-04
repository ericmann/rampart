<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `preferences` holds a PHP serialize()'d blob, read back with unserialize() with no
     * allowed_classes restriction — see docs/VULN-MAP.md (A08). Deliberately NOT json.
     */
    public function up(): void
    {
        Schema::create('saved_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('preferences');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_views');
    }
};
