<?php

namespace App\Notifications;

use App\Models\Engagement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEngagementReceived extends Notification
{
    use Queueable;

    public function __construct(public Engagement $engagement) {}

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
        $engagement = $this->engagement;

        return (new MailMessage)
            ->subject("New engagement: {$engagement->property->title}")
            ->greeting('New engagement received')
            ->line("**{$engagement->name}** ({$engagement->email}) is interested in **{$engagement->property->title}**.")
            ->line($engagement->isGuest() ? 'This is a guest engagement — please reach out to them manually.' : 'This engager has an account and will receive progress updates automatically.')
            ->when($engagement->message, fn (MailMessage $mail) => $mail->line("Message: \"{$engagement->message}\""))
            ->action('Open engagement', route('admin.engagements.show', $engagement));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => "New engagement from {$this->engagement->name}",
            'body' => $this->engagement->property->title,
            'url' => route('admin.engagements.show', $this->engagement),
        ];
    }
}
