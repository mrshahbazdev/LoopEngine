<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('run_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained('process_runs')->cascadeOnDelete();
            $table->foreignId('step_id')->constrained('process_steps')->cascadeOnDelete();
            $table->foreignId('option_id')->nullable()->constrained('step_options')->nullOnDelete();
            $table->text('response_text')->nullable();
            $table->foreignId('responded_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('loop_iteration')->default(1);
            $table->timestamp('responded_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('run_responses');
    }
};
