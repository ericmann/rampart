<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained();
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject');
            $table->text('body');
            $table->string('status')->default('open'); // open, pending, resolved, closed
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->timestamps();

            $table->index(['status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
