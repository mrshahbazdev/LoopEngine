<?php

namespace App\Notifications;

use App\Models\ProcessRun;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProcessRunCompleted extends Notification
{
    use Queueable;

    public function __construct(
        protected ProcessRun $run,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $process = $this->run->process;

        return (new MailMessage)
            ->subject(__('app.run_completed_subject', ['process' => $process->localizedName()]))
            ->greeting(__('app.hello', ['name' => $notifiable->name]))
            ->line(__('app.run_completed_body', [
                'process' => $process->localizedName(),
                'loops' => $this->run->loop_count,
            ]))
            ->action(__('app.run_summary'), url('/runs/' . $this->run->id . '/summary'))
            ->line(__('app.run_completed_footer'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'run_id' => $this->run->id,
            'process_id' => $this->run->process_id,
            'process_name' => $this->run->process->localizedName(),
            'loop_count' => $this->run->loop_count,
            'completed_at' => $this->run->completed_at->toISOString(),
        ];
    }
}
