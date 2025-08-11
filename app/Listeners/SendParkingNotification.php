<?php

namespace App\Listeners;

use App\Events\ParkingEvent;
use App\Models\User;
use App\Notifications\ParkingNotification;

class SendParkingNotification
{
    public function handle(ParkingEvent $event): void
    {
        if ($event->targetRole === 'admin') {
            $admins = User::role('admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new ParkingNotification(
                    title: $event->title,
                    message: $event->message,
                    type: $event->type,
                    link: $event->link,
                    initiatorId: $event->initiatorId,
                    targetRole: $event->targetRole,
                    targetUserId: null,
                ));
            }
            return;
        }

        if ($event->targetRole === 'attendant' && $event->targetUserId) {
            $user = User::find($event->targetUserId);
            if ($user) {
                $user->notify(new ParkingNotification(
                    title: $event->title,
                    message: $event->message,
                    type: $event->type,
                    link: $event->link,
                    initiatorId: $event->initiatorId,
                    targetRole: $event->targetRole,
                    targetUserId: $event->targetUserId,
                ));
            }
        }
    }
}


