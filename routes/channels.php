<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin', fn($user) => $user->hasRole('admin'));

Broadcast::channel('attendant.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id && $user->hasRole('attendant');
});
