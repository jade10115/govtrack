<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_logs', function (Blueprint $table) {

            $table->id();

            $table->foreignId('request_id')
                ->constrained('client_requests')
                ->cascadeOnDelete();

            $table->string('action');

            $table->text('remarks')->nullable();

            $table->foreignId('performed_by')
                ->constrained('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};