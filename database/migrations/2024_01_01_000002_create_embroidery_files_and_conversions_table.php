<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Embroidery file library
        Schema::create('embroidery_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('extension', 10);
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->enum('type', ['original', 'converted'])->default('original');
            $table->foreignId('parent_id')->nullable()->constrained('embroidery_files')->nullOnDelete();

            // Design metadata
            $table->integer('stitch_count')->nullable();
            $table->integer('color_count')->nullable();
            $table->json('thread_colors')->nullable();
            $table->decimal('width_mm', 8, 2)->nullable();
            $table->decimal('height_mm', 8, 2)->nullable();
            $table->string('hoop_size')->nullable();
            $table->json('metadata')->nullable();

            // Preview
            $table->string('preview_path')->nullable();
            $table->boolean('preview_generated')->default(false);

            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'extension']);
        });

        // Conversion jobs / history
        Schema::create('conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_file_id')->nullable()->constrained('embroidery_files')->nullOnDelete();
            $table->foreignId('output_file_id')->nullable()->constrained('embroidery_files')->nullOnDelete();
            $table->string('source_format', 10);
            $table->string('target_format', 10);
            $table->string('original_filename');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('job_id')->nullable();
            $table->text('error_message')->nullable();
            $table->json('warnings')->nullable();
            $table->unsignedInteger('processing_time_ms')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });

        // Conversion usage tracking
        Schema::create('conversion_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversion_usage');
        Schema::dropIfExists('conversions');
        Schema::dropIfExists('embroidery_files');
    }
};
