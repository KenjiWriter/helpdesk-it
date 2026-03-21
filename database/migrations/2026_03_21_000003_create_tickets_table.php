<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->foreignId('assignee_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Enums stored as strings
            $table->string('priority')->default('normal');
            $table->string('category');
            $table->string('status')->default('new');

            // Content
            $table->text('description');
            $table->string('hardware_name')->nullable();

            // Resolution
            $table->timestamp('resolved_at')->nullable();

            // User ratings (1–6 scale)
            $table->tinyInteger('rating_time')->nullable()->unsigned();
            $table->tinyInteger('rating_quality')->nullable()->unsigned();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
