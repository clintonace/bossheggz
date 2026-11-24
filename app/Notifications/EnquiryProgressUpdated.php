<?php

namespace App\Notifications;

use App\Models\EnquiryUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a signed-in client that the team has updated one of their enquiries
 * (land banking, investments, a property or a consultation).
 */
class EnquiryProgressUpdated extends Notification
{
    use Queueable;

    public function __construct(public EnquiryUpdate $update) {}

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
        $enquiry = $this->update->enquiry;

        $message = (new MailMessage)
            ->subject("Update on your {$enquiry->area()->label()} enquiry")
            ->greeting("Hello {$enquiry->name},")
            ->line("There is a new update on your enquiry about **{$enquiry->subject()}**.")
            ->line("**Status:** {$this->update->status->clientLabel()} — {$this->update->status->description()}");

        if ($this->update->message) {
            $message->line("**Message from your adviser:** {$this->update->message}");
        }

        return $message
            ->action('View enquiry', route('account.enquiries.show', $enquiry))
            ->line('Thank you for choosing us.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $enquiry = $this->update->enquiry;

        return [
            'title' => "{$enquiry->subject()}: {$this->update->status->clientLabel()}",
            'body' => $this->update->message ?: $this->update->status->description(),
            'url' => route('account.enquiries.show', $enquiry),
            'status' => $this->update->status->value,
            'kind' => 'enquiry',
            'area' => $enquiry->area()->value,
        ];
    }
}
