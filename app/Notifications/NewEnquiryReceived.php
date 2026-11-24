<?php

namespace App\Notifications;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEnquiryReceived extends Notification
{
    use Queueable;

    public function __construct(public Enquiry $enquiry) {}

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
        $enquiry = $this->enquiry;

        return (new MailMessage)
            ->subject("New {$enquiry->typeLabel()}: {$enquiry->subject()}")
            ->greeting('New enquiry received')
            ->line("**{$enquiry->name}** ({$enquiry->email}".($enquiry->phone ? ", {$enquiry->phone}" : '').") sent a {$enquiry->typeLabel()}.")
            ->line("Regarding: {$enquiry->subject()}")
            ->when($enquiry->message, fn (MailMessage $mail) => $mail->line("Message: \"{$enquiry->message}\""))
            ->action('Open enquiry', route('admin.enquiries.show', $enquiry));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => "New enquiry from {$this->enquiry->name}",
            'body' => "{$this->enquiry->typeLabel()} · {$this->enquiry->subject()}",
            'url' => route('admin.enquiries.show', $this->enquiry),
        ];
    }
}
