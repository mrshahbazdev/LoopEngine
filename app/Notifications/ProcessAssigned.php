<?php

namespace App\Notifications;

use App\Models\TeamAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProcessAssigned extends Notification
{
    use Queueable;

    public function __construct(
        protected TeamAssignment $assignment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $process = $this->assignment->process;
        $assigner = $this->assignment->assigner;

        return (new MailMessage)
            ->subject(__('app.process_assigned_subject', ['process' => $process->localizedName()]))
            ->greeting(__('app.hello', ['name' => $notifiable->name]))
            ->line(__('app.process_assigned_body', [
                'assigner' => $assigner->name,
                'process' => $process->localizedName(),
            ]))
            ->action(__('app.start_run'), url('/runs/start/' . $process->id))
            ->line(__('app.process_assigned_footer'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'process_id' => $this->assignment->process_id,
            'process_name' => $this->assignment->process->localizedName(),
            'assigned_by' => $this->assignment->assigner->name,
            'notes' => $this->assignment->notes,
        ];
    }
}
