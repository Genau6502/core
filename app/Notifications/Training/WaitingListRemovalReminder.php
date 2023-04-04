<?php

namespace App\Notifications\Training;

use App\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class WaitingListRemovalReminder extends Notification implements ShouldQueue
{
    use Queueable;

    private string $list_name;
    private Carbon $removal_date;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $list_name, Carbon $removal_date)
    {
        parent::__construct();

        $this->list_name = $list_name;
        $this->removal_date = $removal_date;
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
        $remainingDays = Carbon::parse(Carbon::now())->diffInDays($this->removal_date);
        $subject = 'You will be removed from a waiting list soon';

        return (new MailMessage)
            ->from('atc-team@vatsim.uk', 'VATSIM UK - ATC Training')
            ->subject($subject)
            ->view('emails.training.waiting_list_removal_reminder', ['removal_date' => $this->removal_date, 'remaining_days' => $remainingDays, 'list_name' => $this->list_name, 'recipient' => $notifiable, 'subject' => $subject]);
    }
}
