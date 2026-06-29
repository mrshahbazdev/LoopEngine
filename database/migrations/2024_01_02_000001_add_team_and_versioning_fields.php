<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add team assignment to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('team')->nullable()->after('locale');
        });

        // Add versioning fields to processes
        Schema::table('processes', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('icon');
            $table->boolean('is_latest_version')->default(true)->after('parent_id');
            $table->foreign('parent_id')->references('id')->on('processes')->nullOnDelete();
        });

        // Add team assignment to process_runs
        Schema::table('process_runs', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('started_by')->constrained('users')->nullOnDelete();
        });

        // Create team_assignments table
        Schema::create('team_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('completed_at')->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, completed
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['process_id', 'user_id']);
        });

        // Create notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('team_assignments');

        Schema::table('process_runs', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn('assigned_to');
        });

        Schema::table('processes', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'is_latest_version']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('team');
        });
    }
};
