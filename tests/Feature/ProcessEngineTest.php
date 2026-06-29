<?php

namespace Tests\Feature;

use App\Models\Process;
use App\Models\ProcessRun;
use App\Models\ProcessStep;
use App\Models\StepOption;
use App\Models\StepTransition;
use App\Models\User;
use App\Services\ProcessEngine;
use App\Services\WebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessEngineTest extends TestCase
{
    use RefreshDatabase;

    protected ProcessEngine $engine;
    protected User $admin;
    protected Process $process;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new ProcessEngine(new WebhookService());
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    protected function createSimpleProcess(): Process
    {
        $process = Process::create([
            'name_en' => 'Test Process',
            'name_de' => 'Testprozess',
            'created_by' => $this->admin->id,
            'status' => 'active',
            'version' => 1,
        ]);

        $step1 = ProcessStep::create([
            'process_id' => $process->id,
            'order' => 0,
            'question_en' => 'First question?',
            'step_type' => 'question',
        ]);

        $step2 = ProcessStep::create([
            'process_id' => $process->id,
            'order' => 1,
            'question_en' => 'Second question?',
            'step_type' => 'question',
        ]);

        $option1 = StepOption::create([
            'step_id' => $step1->id,
            'label_en' => 'Yes',
            'value' => 'yes',
            'order' => 0,
        ]);

        StepTransition::create([
            'step_id' => $step1->id,
            'option_id' => $option1->id,
            'action_type' => 'next_step',
        ]);

        $option2 = StepOption::create([
            'step_id' => $step2->id,
            'label_en' => 'Done',
            'value' => 'done',
            'order' => 0,
        ]);

        StepTransition::create([
            'step_id' => $step2->id,
            'option_id' => $option2->id,
            'action_type' => 'end',
        ]);

        return $process;
    }

    protected function createLoopProcess(): Process
    {
        $process = Process::create([
            'name_en' => 'Loop Process',
            'created_by' => $this->admin->id,
            'status' => 'active',
            'version' => 1,
        ]);

        $step1 = ProcessStep::create([
            'process_id' => $process->id,
            'order' => 0,
            'question_en' => 'Do the work',
            'step_type' => 'question',
        ]);

        $step2 = ProcessStep::create([
            'process_id' => $process->id,
            'order' => 1,
            'question_en' => 'Quality OK?',
            'step_type' => 'loop_check',
            'is_loop_checkpoint' => true,
            'max_loops' => 3,
        ]);

        $optionWork = StepOption::create([
            'step_id' => $step1->id,
            'label_en' => 'Done',
            'value' => 'done',
            'order' => 0,
        ]);

        StepTransition::create([
            'step_id' => $step1->id,
            'option_id' => $optionWork->id,
            'action_type' => 'next_step',
        ]);

        $optionYes = StepOption::create([
            'step_id' => $step2->id,
            'label_en' => 'Yes',
            'value' => 'yes',
            'order' => 0,
        ]);

        StepTransition::create([
            'step_id' => $step2->id,
            'option_id' => $optionYes->id,
            'action_type' => 'end',
        ]);

        $optionNo = StepOption::create([
            'step_id' => $step2->id,
            'label_en' => 'No',
            'value' => 'no',
            'order' => 1,
        ]);

        StepTransition::create([
            'step_id' => $step2->id,
            'option_id' => $optionNo->id,
            'action_type' => 'loop_back',
            'target_step_id' => $step1->id,
        ]);

        return $process;
    }

    public function test_start_run(): void
    {
        $process = $this->createSimpleProcess();
        $run = $this->engine->startRun($process, $this->admin);

        $this->assertInstanceOf(ProcessRun::class, $run);
        $this->assertEquals('in_progress', $run->status);
        $this->assertEquals(0, $run->loop_count);
        $this->assertNotNull($run->current_step_id);
    }

    public function test_submit_answer_moves_to_next_step(): void
    {
        $process = $this->createSimpleProcess();
        $run = $this->engine->startRun($process, $this->admin);

        $step = $run->currentStep;
        $option = $step->options->first();

        $result = $this->engine->submitAnswer($run, $step, $option, null, $this->admin);

        $this->assertEquals('next_step', $result['action']);
        $this->assertEquals(1, $result['step']->order);
    }

    public function test_end_run(): void
    {
        $process = $this->createSimpleProcess();
        $run = $this->engine->startRun($process, $this->admin);

        // Answer step 1
        $step1 = $run->currentStep;
        $this->engine->submitAnswer($run, $step1, $step1->options->first(), null, $this->admin);

        // Answer step 2 (ends)
        $run->refresh();
        $step2 = $run->currentStep;
        $result = $this->engine->submitAnswer($run, $step2, $step2->options->first(), null, $this->admin);

        $this->assertEquals('end', $result['action']);
        $this->assertEquals('completed', $result['run']->status);
    }

    public function test_loop_back(): void
    {
        $process = $this->createLoopProcess();
        $run = $this->engine->startRun($process, $this->admin);

        // Answer step 1
        $step1 = $run->currentStep;
        $this->engine->submitAnswer($run, $step1, $step1->options->first(), null, $this->admin);

        // Answer step 2 with "No" (loop back)
        $run->refresh();
        $step2 = $run->currentStep;
        $noOption = $step2->options->where('value', 'no')->first();

        $result = $this->engine->submitAnswer($run, $step2, $noOption, null, $this->admin);

        $this->assertEquals('loop_back', $result['action']);
        $this->assertEquals(1, $result['run']->loop_count);
        $this->assertEquals($step1->id, $result['run']->current_step_id);
    }

    public function test_max_loops_enforced(): void
    {
        $process = $this->createLoopProcess();
        $run = $this->engine->startRun($process, $this->admin);

        $steps = $process->steps()->get();
        $step1 = $steps[0];
        $step2 = $steps[1];
        $noOption = $step2->options->where('value', 'no')->first();
        $doneOption = $step1->options->first();

        // Loop 3 times (max_loops = 3)
        for ($i = 0; $i < 3; $i++) {
            $run->refresh();
            $this->engine->submitAnswer($run, $step1, $doneOption, null, $this->admin);
            $run->refresh();
            $this->engine->submitAnswer($run, $step2, $noOption, null, $this->admin);
        }

        // 4th time: step1 answer, then step2 with "No" should NOT loop back (max reached)
        $run->refresh();
        $this->engine->submitAnswer($run, $step1, $doneOption, null, $this->admin);
        $run->refresh();
        $result = $this->engine->submitAnswer($run, $step2, $noOption, null, $this->admin);

        // Should end because there's no step after step2 and max loops reached
        $this->assertEquals('end', $result['action']);
    }

    public function test_pause_and_resume(): void
    {
        $process = $this->createSimpleProcess();
        $run = $this->engine->startRun($process, $this->admin);

        $paused = $this->engine->pauseRun($run, $this->admin);
        $this->assertEquals('paused', $paused->status);

        $resumed = $this->engine->resumeRun($paused, $this->admin);
        $this->assertEquals('in_progress', $resumed->status);
    }

    public function test_cancel_run(): void
    {
        $process = $this->createSimpleProcess();
        $run = $this->engine->startRun($process, $this->admin);

        $cancelled = $this->engine->cancelRun($run, $this->admin);
        $this->assertEquals('cancelled', $cancelled->status);
        $this->assertNotNull($cancelled->completed_at);
    }

    public function test_audit_trail_created(): void
    {
        $process = $this->createSimpleProcess();
        $run = $this->engine->startRun($process, $this->admin);

        $this->assertDatabaseHas('run_logs', [
            'run_id' => $run->id,
            'action' => 'started',
        ]);

        $step = $run->currentStep;
        $this->engine->submitAnswer($run, $step, $step->options->first(), null, $this->admin);

        $this->assertDatabaseHas('run_logs', [
            'run_id' => $run->id,
            'action' => 'answered',
        ]);
    }
}
