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

    /**
     * Prevent duplicate unread notifications in the database for same payload.
     * Applies only to the database channel; broadcast will still emit.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        if ($channel !== 'database') {
            return true;
        }

        return ! $notifiable->notifications()
            ->where('type', static::class)
            ->whereNull('read_at')
            ->where('data->title', $this->title)
            ->where('data->message', $this->message)
            ->where('data->type', $this->type)
            ->where('data->link', $this->link)
            ->where('data->initiator_id', $this->initiatorId)
            ->where('data->target_role', $this->targetRole)
            ->exists();
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

    /**
     * Payload stored by the database channel.
     */
    public function toDatabase(object $notifiable): array
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


