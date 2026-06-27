<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('step_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('step_id')->constrained('process_steps')->cascadeOnDelete();
            $table->foreignId('option_id')->nullable()->constrained('step_options')->cascadeOnDelete();
            $table->string('action_type'); // next_step, goto_step, start_process, loop_back, end
            $table->foreignId('target_step_id')->nullable()->constrained('process_steps')->nullOnDelete();
            $table->foreignId('target_process_id')->nullable()->constrained('processes')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('step_transitions');
    }
};
