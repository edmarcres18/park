<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParkingEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $action, // session_started | session_ended | ticket_generated
        public string $title,
        public string $message,
        public string $type = 'info', // info | success | warning
        public ?string $link = null,
        public ?int $initiatorId = null,
        public string $targetRole = 'admin', // admin | attendant
        public ?int $targetUserId = null,
    ) {
    }
}


