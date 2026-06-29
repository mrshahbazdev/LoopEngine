<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Webhooks table
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('url');
            $table->string('secret')->nullable();
            $table->json('events'); // ['run.completed', 'run.started', 'run.looped_back', 'process.activated']
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('failure_count')->default(0);
            $table->timestamps();
        });

        // Webhook logs
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->json('payload');
            $table->integer('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->boolean('success')->default(false);
            $table->timestamps();
        });

        // Process templates (marketplace)
        Schema::create('process_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shared_by')->constrained('users')->cascadeOnDelete();
            $table->string('name_en');
            $table->string('name_de')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_de')->nullable();
            $table->string('category')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_public')->default(true);
            $table->integer('install_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->timestamps();
        });

        // Template ratings
        Schema::create('template_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('process_templates')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('rating'); // 1-5
            $table->text('review')->nullable();
            $table->timestamps();

            $table->unique(['template_id', 'user_id']);
        });

        // Permissions table for granular RBAC
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('permission'); // e.g. 'processes.create', 'runs.export', 'admin.users'
            $table->timestamps();

            $table->unique(['user_id', 'permission']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('template_ratings');
        Schema::dropIfExists('process_templates');
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('webhooks');
    }
};
