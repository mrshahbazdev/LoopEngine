<?php

namespace Tests\Feature;

use App\Models\Process;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_team_dashboard_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('team.dashboard'));
        $response->assertOk();
    }

    public function test_assign_process_to_user(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $process = Process::create([
            'name_en' => 'Test',
            'created_by' => $this->admin->id,
            'status' => 'active',
            'version' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('team.assign'), [
                'process_id' => $process->id,
                'user_id' => $employee->id,
                'notes' => 'Please complete ASAP',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('team_assignments', [
            'process_id' => $process->id,
            'user_id' => $employee->id,
            'status' => 'pending',
        ]);
    }

    public function test_employee_cannot_assign(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $process = Process::create([
            'name_en' => 'Test',
            'created_by' => $this->admin->id,
            'status' => 'active',
            'version' => 1,
        ]);

        $response = $this->actingAs($employee)
            ->post(route('team.assign'), [
                'process_id' => $process->id,
                'user_id' => $this->admin->id,
            ]);

        $response->assertStatus(403);
    }
}
