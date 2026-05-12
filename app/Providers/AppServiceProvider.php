<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        VerifyEmail::createUrlUsing(function (User $notifiable){
            return URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
        });

        VerifyEmail::toMailUsing(function ($notifiable, string $url){
            return (new MailMessage)
                ->subject('Confirme seu e-email')
                ->line('Clique no botão abaixo para confirmar seu e-mail.')
                ->action('Confirmar e-mail', $url)
                ->line('Se você não criou esta conta, ignore este e-mail.');
        });
    }
}
