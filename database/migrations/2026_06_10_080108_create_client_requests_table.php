<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_requests', function (Blueprint $table) {

            $table->id();

            $table->string('control_no')->unique();

            $table->string('client_name');

            $table->string('address');

            $table->string('contact_number');

            $table->integer('age');

            $table->string('gender');

            $table->foreignId('request_type_id')
                ->constrained();

            $table->foreignId('department_id')
                ->constrained();

            $table->foreignId('assigned_to')
                ->nullable()
                ->references('id')
                ->on('users');

            $table->foreignId('created_by')
                ->references('id')
                ->on('users');

            $table->longText('interview_notes');

            $table->enum('status',[
                'Pending',
                'Accepted',
                'Rejected',
                'Completed'
            ])->default('Pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_requests');
    }
};