<?php

namespace App\Notifications;

use App\Models\Engagement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PropertyUpdated extends Notification
{
    use Queueable;

    /**
     * @param  array<int, string>  $changes  Human readable change lines, e.g. "Price changed to ₦50,000,000".
     */
    public function __construct(public Engagement $engagement, public array $changes) {}

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
        $property = $this->engagement->property;

        $message = (new MailMessage)
            ->subject("{$property->title} has been updated")
            ->greeting("Hello {$notifiable->name},")
            ->line("A property you engaged with, **{$property->title}**, has changed:");

        foreach ($this->changes as $change) {
            $message->line("• {$change}");
        }

        return $message
            ->action('View property', route('properties.show', $property))
            ->line('You are receiving this because you engaged with this property.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => "{$this->engagement->property->title} was updated",
            'body' => implode(' · ', $this->changes),
            'url' => route('account.engagements.show', $this->engagement),
        ];
    }
}
