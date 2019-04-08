<?php

namespace App\Listeners;

use App\Events\CompanyRegister;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mail;

class SendRegisterEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CompanyRegister  $event
     * @return void
     */
    public function handle(CompanyRegister $event)
    {
        $email = $event->emailToken->email;
        $token = $event->emailToken->token;
        $host  = $event->emailToken->host;
        Mail::send('registerSuccessMail', ['token' => $token, 'host' => $host], function ($message) use ($email){
            $message->to($email);
            $subject = "=?UTF-8?B?".base64_encode("com邮箱激活")."?=";//邮件主题
            $message->subject($subject);
        });
        return false;
    }
}
