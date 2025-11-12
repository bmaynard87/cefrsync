<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Your CEFRSync Account')
            ->greeting("Welcome to CEFRSync, {$notifiable->first_name}!")
            ->line('Thank you for creating your language learning account. We\'re excited to help you on your journey to fluency!')
            ->line('To get started with personalized language practice and AI-powered conversations, please verify your email address by clicking the button below.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('Once verified, you\'ll be able to:')
            ->line('✓ Start AI-powered conversations in your target language')
            ->line('✓ Track your progress across CEFR levels (A1-C2)')
            ->line('✓ Practice at your own pace with adaptive difficulty')
            ->line('If you did not create an account, no further action is required.')
            ->salutation("Happy learning!\nThe CEFRSync Team");
    }
}
