<?php

namespace App\Listeners;

use App\Events\CompanyForgetPass;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mail;

class CompanyForgetPassListener
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
     * @param  CompanyForgetPass  $event
     * @return void
     */
    public function handle(CompanyForgetPass $event)
    {
        $email = $event->emailToken->email;
        $token = $event->emailToken->token;
        $host  = $event->emailToken->host;
        $companyName  = $event->emailToken->companyName;
        Mail::send('companyForgetPassMail', ['token' => $token, 'host' => $host, 'companyName' => $companyName], function ($message) use ($email){
            $message->to($email);
            $subject = "=?UTF-8?B?".base64_encode("密码重置")."?=";//邮件主题
            $message->subject($subject);
        });
        return false;
    }
}
