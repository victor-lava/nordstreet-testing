<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;

class RunningQueue extends Notification
{
    use Queueable;

    protected $name;
    protected $status;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $name, int $status)
    {
        $this->name = $name;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        $name = $this->name;
        $status = ($this->status === 0) ? 'Running' : 'Ending';

        return (new SlackMessage)
                      ->content("$status automated queue \"$name\".")
                      ->attachment(function ($attachment) use ($status, $name) {
                         $attachment->fields([
                                         'Date' => date('Y-m-d H:i:s'),
                                         'Status' => $status
                                     ]);
                     });
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
