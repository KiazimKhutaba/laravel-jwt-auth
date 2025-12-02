<?php

namespace Devkit2026\JwtAuth\Listeners;

use Devkit2026\JwtAuth\Events\UserRegistered;
use Devkit2026\JwtAuth\Mail\VerifyEmailMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendVerificationEmail implements ShouldQueue
{
    public function handle(UserRegistered $event): void
    {
        $url = URL::temporarySignedRoute(
            'jwt.verify',
            now()->addMinutes(60),
            ['id' => $event->user->id, 'hash' => sha1($event->user->getEmailForVerification())]
        );

        Mail::to($event->user->email)->send(new VerifyEmailMail($event->user, $url));
    }
}
