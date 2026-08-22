<?php

namespace App\Models;

use Eloquent;

class ActivityLog extends Eloquent
{
    protected $fillable = ['user_id', 'action', 'description', 'properties', 'ip_address'];

    protected $casts = ['properties' => 'array'];

    public static function record(string $action, string $description = null, array $properties = []): self
    {
        return static::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'description' => $description,
            'properties'  => $properties ?: null,
            'ip_address'  => request() ? request()->ip() : null,
        ]);
    }

    public function user()
    {
        $userClass = '\\' . ltrim(config('auth.providers.users.model', \App\User::class), '\\');

        return $this->belongsTo($userClass);
    }
}
