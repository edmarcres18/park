<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;

class ParkingNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $type = 'info',
        public ?string $link = null,
        public ?int $initiatorId = null,
        public string $targetRole = 'admin',
        public ?int $targetUserId = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'link' => $this->link,
            'initiator_id' => $this->initiatorId,
            'target_role' => $this->targetRole,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => (string) $this->id,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'link' => $this->link,
            'initiator_id' => $this->initiatorId,
            'target_role' => $this->targetRole,
            'created_at' => now()->toISOString(),
            'read' => false,
        ]);
    }

    public function broadcastOn(): array
    {
        if ($this->targetRole === 'attendant') {
            $id = $this->targetUserId;
            if (!$id) {
                return [new PrivateChannel('attendant.unknown')];
            }
            return [new PrivateChannel('attendant.' . $id)];
        }

        return [new PrivateChannel('admin')];
    }

    public function broadcastType(): string
    {
        return 'parking.notification';
    }
}


