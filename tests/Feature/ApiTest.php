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

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create();
        $this->user = User::factory()->create(['role' => 'employee', 'company_id' => $this->company->id]);
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    protected function createActiveProcess(): Process
    {
        $admin = User::factory()->create(['role' => 'admin', 'company_id' => $this->company->id]);

        $process = Process::create([
            'name_en' => 'API Test Process',
            'created_by' => $admin->id,
            'company_id' => $this->company->id,
            'status' => 'active',
            'version' => 1,
        ]);

        $step = ProcessStep::create([
            'process_id' => $process->id,
            'order' => 0,
            'question_en' => 'Test question?',
            'step_type' => 'question',
        ]);

        $option = StepOption::create([
            'step_id' => $step->id,
            'label_en' => 'Yes',
            'value' => 'yes',
            'order' => 0,
        ]);

        StepTransition::create([
            'step_id' => $step->id,
            'option_id' => $option->id,
            'action_type' => 'end',
        ]);

        return $process;
    }

    public function test_api_requires_auth(): void
    {
        $response = $this->getJson('/api/processes');
        $response->assertStatus(401);
    }

    public function test_list_processes(): void
    {
        $this->createActiveProcess();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/processes');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_show_process(): void
    {
        $process = $this->createActiveProcess();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/processes/' . $process->id);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'API Test Process');
    }

    public function test_start_and_complete_run(): void
    {
        $process = $this->createActiveProcess();

        // Start run
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/runs/start/' . $process->id);

        $response->assertStatus(201);
        $runId = $response->json('data.id');

        // Submit answer (ends the run)
        $optionId = $response->json('data.current_step.options.0.id');
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/runs/' . $runId . '/answer', [
                'option_id' => $optionId,
            ]);

        $response->assertOk();
        $response->assertJsonPath('action', 'end');
        $response->assertJsonPath('data.status', 'completed');
    }

    public function test_get_user(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/user');

        $response->assertOk();
        $response->assertJsonPath('data.email', $this->user->email);
    }
}
