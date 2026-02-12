<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationNotification extends Notification 
{

    use Queueable;
    /**
     * Create a new notification instance.
     */
    public $user;
    public $verificationCode;
    public function __construct($user,$verificationCode)
    {
        //
        $this->user = $user;
        $this->verificationCode = $verificationCode;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
{
    $appName = config('app.name');
    $verificationCode = $this->verificationCode; 

    return (new MailMessage)
        ->subject("Verify Your Email Address")
        ->greeting("Welcome to {$appName}!")
        ->line('Thanks for registering with us')
        ->line('To complete your registration, please use the verification code below:')
        ->line("**{$verificationCode}**")
        ->line('This code will expire in 10 minutes.')
        ->line('If you did not create an account, no further action is required.')
        ->salutation("Regards,\n{$appName} Team");
}


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
