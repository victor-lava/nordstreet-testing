<?php

namespace App\Notifications\Admin\Check;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;

class MissingInvestment extends Notification
{
    // use Queueable;

    protected $investment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $investment)
    {
        $this->investment = $investment;
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
        $investment = $this->investment;
        $fullName = $investment['user']->name . ' '. $investment['user']->lastname;
        $title = "$fullName, ".strtolower($investment['user']->email).', '.$investment['project']->namelt;
        $url = "https://nordstreet.com/in/admin/".$investment['user']->id;

        return (new SlackMessage)
                      ->{($investment['type'] === 'error') ? 'error' : 'warning'}()
                      ->content($this->investment['message'])
                      ->attachment(function ($attachment) use ($investment, $title, $url) {
                         $attachment->title($title, $url)
                                    ->fields([
                                         'Project' => "#".$investment['project']->id,
                                         'User' => "#".$investment['user']->id,
                                         'Paid' => $investment['total_payments'],
                                         'Investment' => $investment['investment'],
                                     ]);
                     });
        // 
        //
        // return (new SlackMessage)
        //             ->content($this->investment['message'])
        //             ->attachment(function ($attachment) use ($investment, $title, $url) {
        //                $attachment->title($title, $url)
        //                           ->fields([
        //                                'Project' => "#".$investment['project']->id,
        //                                'User' => "#".$investment['user']->id,
        //                                'Paid' => $investment['total_payments'],
        //                                'Investment' => $investment['investment'],
        //                            ]);
        //            });
    }

    public function routeNotificationForSlack($notification)
    {
        return 'https://hooks.slack.com/services/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';
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
