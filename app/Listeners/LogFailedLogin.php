<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;

class LogFailedLogin
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param  Failed  $event
     * @return void
     */
    public function handle(Failed $event)
    {
        activity('auth')
            ->withProperties([
                'email' => $this->request->email,
                'ip' => $this->request->ip(),
                'user_agent' => $this->request->header('User-Agent'),
            ])
            ->log('User failed to log in');
    }
}
