<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('target_url'); // outbound: admin "test webhook" fetches this (SSRF surface, secondary)
            $table->string('event')->default('ticket.created');
            $table->string('inbound_token')->unique(); // public path segment for the inbound receiver
            $table->string('secret'); // meant for HMAC verification — inbound receiver never checks it (A08)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $table->string('direction'); // inbound | outbound
            $table->json('payload')->nullable();
            $table->string('result')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhooks');
    }
};
