<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'code',
        'address',
    ];

    /**
     * Get all users (attendants) that belong to this branch
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get only attendant users for this branch
     */
    public function attendants(): HasMany
    {
        return $this->hasMany(User::class)->whereHas('roles', function ($query) {
            $query->where('name', 'attendant');
        });
    }
}
