<?php

namespace Database\Seeders;

use App\Models\Process;
use App\Models\ProcessStep;
use App\Models\StepOption;
use App\Models\StepTransition;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create users
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@loopengine.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'locale' => 'en',
        ]);

        $teamLead = User::create([
            'name' => 'Team Lead',
            'email' => 'lead@loopengine.test',
            'password' => Hash::make('password'),
            'role' => 'team_lead',
            'locale' => 'en',
        ]);

        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@loopengine.test',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'locale' => 'en',
        ]);

        // Create a sample process: Quality Check Loop
        $process = Process::create([
            'name_en' => 'Quality Check Process',
            'name_de' => 'Qualitätsprüfung Prozess',
            'description_en' => 'A self-checking quality assurance process with feedback loops.',
            'description_de' => 'Ein selbstprüfender Qualitätssicherungsprozess mit Regelkreisläufen.',
            'created_by' => $admin->id,
            'status' => 'active',
            'version' => 1,
            'category' => 'Quality',
        ]);

        // Step 1: Task Complete?
        $step1 = ProcessStep::create([
            'process_id' => $process->id,
            'order' => 0,
            'question_en' => 'Has the task been completed?',
            'question_de' => 'Wurde die Aufgabe abgeschlossen?',
            'help_text_en' => 'Confirm that all required work has been done.',
            'help_text_de' => 'Bestätige, dass alle erforderlichen Arbeiten erledigt sind.',
            'step_type' => 'question',
        ]);

        $s1_yes = StepOption::create([
            'step_id' => $step1->id, 'label_en' => 'Yes', 'label_de' => 'Ja',
            'value' => 'yes', 'order' => 0, 'color' => 'green',
        ]);
        $s1_no = StepOption::create([
            'step_id' => $step1->id, 'label_en' => 'No, not yet', 'label_de' => 'Nein, noch nicht',
            'value' => 'no', 'order' => 1, 'color' => 'red',
        ]);

        // Step 2: Quality Review
        $step2 = ProcessStep::create([
            'process_id' => $process->id,
            'order' => 1,
            'question_en' => 'Does the result meet quality standards?',
            'question_de' => 'Entspricht das Ergebnis den Qualitätsstandards?',
            'help_text_en' => 'Check all quality criteria before proceeding.',
            'help_text_de' => 'Überprüfe alle Qualitätskriterien vor dem Fortfahren.',
            'step_type' => 'loop_check',
            'is_loop_checkpoint' => true,
            'max_loops' => 3,
        ]);

        $s2_pass = StepOption::create([
            'step_id' => $step2->id, 'label_en' => 'Meets standards', 'label_de' => 'Entspricht den Standards',
            'value' => 'pass', 'order' => 0, 'color' => 'green',
        ]);
        $s2_fail = StepOption::create([
            'step_id' => $step2->id, 'label_en' => 'Does not meet standards', 'label_de' => 'Entspricht nicht den Standards',
            'value' => 'fail', 'order' => 1, 'color' => 'red',
        ]);
        $s2_partial = StepOption::create([
            'step_id' => $step2->id, 'label_en' => 'Partially meets standards', 'label_de' => 'Teilweise erfüllt',
            'value' => 'partial', 'order' => 2, 'color' => 'yellow',
        ]);

        // Step 3: Documentation
        $step3 = ProcessStep::create([
            'process_id' => $process->id,
            'order' => 2,
            'question_en' => 'Has the documentation been updated?',
            'question_de' => 'Wurde die Dokumentation aktualisiert?',
            'step_type' => 'question',
        ]);

        $s3_yes = StepOption::create([
            'step_id' => $step3->id, 'label_en' => 'Yes, fully documented', 'label_de' => 'Ja, vollständig dokumentiert',
            'value' => 'yes', 'order' => 0, 'color' => 'green',
        ]);
        $s3_no = StepOption::create([
            'step_id' => $step3->id, 'label_en' => 'No', 'label_de' => 'Nein',
            'value' => 'no', 'order' => 1, 'color' => 'red',
        ]);

        // Step 4: Final Approval
        $step4 = ProcessStep::create([
            'process_id' => $process->id,
            'order' => 3,
            'question_en' => 'Final approval: Is everything ready for delivery?',
            'question_de' => 'Endabnahme: Ist alles bereit für die Lieferung?',
            'step_type' => 'decision',
            'is_loop_checkpoint' => true,
            'max_loops' => 2,
        ]);

        $s4_approve = StepOption::create([
            'step_id' => $step4->id, 'label_en' => 'Approved', 'label_de' => 'Genehmigt',
            'value' => 'approved', 'order' => 0, 'color' => 'green',
        ]);
        $s4_reject = StepOption::create([
            'step_id' => $step4->id, 'label_en' => 'Rejected - needs rework', 'label_de' => 'Abgelehnt - Nacharbeit erforderlich',
            'value' => 'rejected', 'order' => 1, 'color' => 'red',
        ]);

        // Transitions
        // Step 1: Yes → next, No → end
        StepTransition::create([
            'step_id' => $step1->id, 'option_id' => $s1_yes->id,
            'action_type' => 'next_step',
        ]);
        StepTransition::create([
            'step_id' => $step1->id, 'option_id' => $s1_no->id,
            'action_type' => 'end',
        ]);

        // Step 2: Pass → next, Fail → loop back to step 1, Partial → loop back to step 1
        StepTransition::create([
            'step_id' => $step2->id, 'option_id' => $s2_pass->id,
            'action_type' => 'next_step',
        ]);
        StepTransition::create([
            'step_id' => $step2->id, 'option_id' => $s2_fail->id,
            'action_type' => 'loop_back', 'target_step_id' => $step1->id,
        ]);
        StepTransition::create([
            'step_id' => $step2->id, 'option_id' => $s2_partial->id,
            'action_type' => 'loop_back', 'target_step_id' => $step1->id,
        ]);

        // Step 3: Yes → next, No → loop back
        StepTransition::create([
            'step_id' => $step3->id, 'option_id' => $s3_yes->id,
            'action_type' => 'next_step',
        ]);
        StepTransition::create([
            'step_id' => $step3->id, 'option_id' => $s3_no->id,
            'action_type' => 'loop_back', 'target_step_id' => $step3->id,
        ]);

        // Step 4: Approved → end, Rejected → loop back to step 1
        StepTransition::create([
            'step_id' => $step4->id, 'option_id' => $s4_approve->id,
            'action_type' => 'end',
        ]);
        StepTransition::create([
            'step_id' => $step4->id, 'option_id' => $s4_reject->id,
            'action_type' => 'loop_back', 'target_step_id' => $step1->id,
        ]);

        // Create second process: Bug Report Workflow
        $process2 = Process::create([
            'name_en' => 'Bug Report Workflow',
            'name_de' => 'Fehlermeldung Workflow',
            'description_en' => 'Standard workflow for handling bug reports with self-verification.',
            'description_de' => 'Standard-Workflow zur Bearbeitung von Fehlermeldungen mit Selbstverifikation.',
            'created_by' => $teamLead->id,
            'status' => 'active',
            'version' => 1,
            'category' => 'Development',
        ]);

        $b1 = ProcessStep::create([
            'process_id' => $process2->id, 'order' => 0,
            'question_en' => 'Can you reproduce the bug?',
            'question_de' => 'Kannst du den Fehler reproduzieren?',
            'step_type' => 'question',
        ]);
        $b1y = StepOption::create(['step_id' => $b1->id, 'label_en' => 'Yes', 'label_de' => 'Ja', 'value' => 'yes', 'order' => 0, 'color' => 'green']);
        $b1n = StepOption::create(['step_id' => $b1->id, 'label_en' => 'No', 'label_de' => 'Nein', 'value' => 'no', 'order' => 1, 'color' => 'red']);

        $b2 = ProcessStep::create([
            'process_id' => $process2->id, 'order' => 1,
            'question_en' => 'What is the severity level?',
            'question_de' => 'Wie hoch ist der Schweregrad?',
            'step_type' => 'decision',
        ]);
        StepOption::create(['step_id' => $b2->id, 'label_en' => 'Critical', 'label_de' => 'Kritisch', 'value' => 'critical', 'order' => 0, 'color' => 'red']);
        StepOption::create(['step_id' => $b2->id, 'label_en' => 'Major', 'label_de' => 'Schwerwiegend', 'value' => 'major', 'order' => 1, 'color' => 'yellow']);
        StepOption::create(['step_id' => $b2->id, 'label_en' => 'Minor', 'label_de' => 'Gering', 'value' => 'minor', 'order' => 2, 'color' => 'blue']);

        $b3 = ProcessStep::create([
            'process_id' => $process2->id, 'order' => 2,
            'question_en' => 'Has the fix been applied and tested?',
            'question_de' => 'Wurde der Fix angewendet und getestet?',
            'step_type' => 'loop_check',
            'is_loop_checkpoint' => true,
            'max_loops' => 5,
        ]);
        $b3y = StepOption::create(['step_id' => $b3->id, 'label_en' => 'Yes, fixed and verified', 'label_de' => 'Ja, behoben und verifiziert', 'value' => 'fixed', 'order' => 0, 'color' => 'green']);
        $b3n = StepOption::create(['step_id' => $b3->id, 'label_en' => 'No, still broken', 'label_de' => 'Nein, immer noch fehlerhaft', 'value' => 'broken', 'order' => 1, 'color' => 'red']);

        // Transitions for bug workflow
        StepTransition::create(['step_id' => $b1->id, 'option_id' => $b1y->id, 'action_type' => 'next_step']);
        StepTransition::create(['step_id' => $b1->id, 'option_id' => $b1n->id, 'action_type' => 'end']);
        StepTransition::create(['step_id' => $b3->id, 'option_id' => $b3y->id, 'action_type' => 'end']);
        StepTransition::create(['step_id' => $b3->id, 'option_id' => $b3n->id, 'action_type' => 'loop_back', 'target_step_id' => $b1->id]);
    }
}
