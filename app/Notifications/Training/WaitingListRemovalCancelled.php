<?php

namespace App\Notifications\Training;

use App\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class WaitingListRemovalCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    private string $list_name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $list_name)
    {
        parent::__construct();

        $this->list_name = $list_name;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = 'You now meet the eligibility for a waiting list you are a member of';

        return (new MailMessage)
            ->from('atc-team@vatsim.uk', 'VATSIM UK - ATC Training')
            ->subject($subject)
            ->view('emails.training.waiting_list_removal_cancelled', ['list_name' => $this->list_name, 'recipient' => $notifiable, 'subject' => $subject]);
    }
}
