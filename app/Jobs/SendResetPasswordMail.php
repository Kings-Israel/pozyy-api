<?php

namespace App\Jobs;

use App\Mail\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendResetPasswordMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email;
    public $code;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $email, string $code)
    {
        $this->email = $email;
        $this->code = $code;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send(new ResetPassword($this->email, $this->code));
    }
}
