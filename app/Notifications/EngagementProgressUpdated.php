<?php

namespace App\Notifications;

use App\Models\EngagementUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EngagementProgressUpdated extends Notification
{
    use Queueable;

    public function __construct(public EngagementUpdate $update) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $engagement = $this->update->engagement;
        $property = $engagement->property;

        $message = (new MailMessage)
            ->subject("Update on {$property->title}")
            ->greeting("Hello {$engagement->name},")
            ->line("There is a new update on your interest in **{$property->title}**.")
            ->line("**Status:** {$this->update->status->label()} — {$this->update->status->description()}");

        if ($this->update->note) {
            $message->line("**Note from our team:** {$this->update->note}");
        }

        return $message
            ->action('View progress', route('account.engagements.show', $engagement))
            ->line('Thank you for choosing us.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $engagement = $this->update->engagement;

        return [
            'title' => "{$engagement->property->title}: {$this->update->status->label()}",
            'body' => $this->update->note ?: $this->update->status->description(),
            'url' => route('account.engagements.show', $engagement),
            'status' => $this->update->status->value,
        ];
    }
}
