<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Process;
use App\Models\ProcessStep;
use App\Models\StepOption;
use App\Models\StepTransition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessDuplicationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create();
        $this->admin = User::factory()->create(['role' => 'admin', 'company_id' => $this->company->id]);
    }

    protected function createProcess(): Process
    {
        $process = Process::create([
            'name_en' => 'Original Process',
            'name_de' => 'Originalprozess',
            'description_en' => 'A test process',
            'created_by' => $this->admin->id,
            'company_id' => $this->company->id,
            'status' => 'active',
            'version' => 1,
            'category' => 'quality',
        ]);

        $step1 = ProcessStep::create([
            'process_id' => $process->id,
            'order' => 0,
            'question_en' => 'Q1?',
            'step_type' => 'question',
        ]);

        $step2 = ProcessStep::create([
            'process_id' => $process->id,
            'order' => 1,
            'question_en' => 'Q2?',
            'step_type' => 'decision',
            'is_loop_checkpoint' => true,
            'max_loops' => 5,
        ]);

        $option = StepOption::create([
            'step_id' => $step1->id,
            'label_en' => 'Yes',
            'value' => 'yes',
            'order' => 0,
        ]);

        StepTransition::create([
            'step_id' => $step1->id,
            'option_id' => $option->id,
            'action_type' => 'next_step',
        ]);

        StepTransition::create([
            'step_id' => $step2->id,
            'action_type' => 'loop_back',
            'target_step_id' => $step1->id,
        ]);

        return $process;
    }

    public function test_duplicate_process(): void
    {
        $process = $this->createProcess();
        $clone = $process->duplicate($this->admin);

        $this->assertNotEquals($process->id, $clone->id);
        $this->assertEquals('draft', $clone->status);
        $this->assertEquals('Original Process (Copy)', $clone->name_en);
        $this->assertEquals('Originalprozess (Kopie)', $clone->name_de);
        $this->assertEquals(2, $clone->steps()->count());
        $this->assertEquals(1, $clone->steps()->first()->options()->count());
    }

    public function test_duplicate_preserves_transitions(): void
    {
        $process = $this->createProcess();
        $clone = $process->duplicate($this->admin);

        $cloneSteps = $clone->steps()->get();
        $step1Transitions = $cloneSteps[0]->transitions;
        $step2Transitions = $cloneSteps[1]->transitions;

        $this->assertCount(1, $step1Transitions);
        $this->assertEquals('next_step', $step1Transitions[0]->action_type);

        $this->assertCount(1, $step2Transitions);
        $this->assertEquals('loop_back', $step2Transitions[0]->action_type);
        $this->assertEquals($cloneSteps[0]->id, $step2Transitions[0]->target_step_id);
    }

    public function test_create_new_version(): void
    {
        $process = $this->createProcess();
        $newVersion = $process->createNewVersion($this->admin);

        $process->refresh();

        $this->assertFalse($process->is_latest_version);
        $this->assertTrue($newVersion->is_latest_version);
        $this->assertEquals(2, $newVersion->version);
        $this->assertEquals($process->id, $newVersion->parent_id);
        $this->assertEquals('Original Process', $newVersion->name_en);
    }

    public function test_duplicate_via_route(): void
    {
        $process = $this->createProcess();

        $response = $this->actingAs($this->admin)
            ->post(route('processes.duplicate', $process));

        $response->assertRedirect();
        $this->assertDatabaseCount('processes', 2);
    }
}
