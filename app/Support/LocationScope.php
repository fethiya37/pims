<?php

namespace App\Support;

use App\Models\Location;
use Illuminate\Support\Facades\Auth;

class LocationScope
{
    public static function allowedIds(): array
    {
        $user = Auth::user();

        if ($user->role && $user->role->role_name === 'Super Admin') {
            return Location::pluck('id')->toArray();
        }

        return $user->location_id ? [$user->location_id] : [];
    }

    public static function forDropdown()
    {
        return Location::whereIn('id', self::allowedIds())->orderBy('name')->get();
    }

    public static function isSuperAdmin(): bool
    {
        $user = Auth::user();
        return $user && $user->role && $user->role->role_name === 'Super Admin';
    }

    public static function resolveRequestedLocation($requested, array $allowedIds): ?int
    {
        if (self::isSuperAdmin()) {
            if (!empty($requested) && !in_array($requested, $allowedIds)) {
                return null;
            }
            return $requested ? (int) $requested : null;
        }

        $userId = Auth::user()->location_id;

        if (empty($requested)) {
            return $userId;
        }

        if (!in_array($requested, $allowedIds)) {
            return $userId;
        }

        return (int) $requested;
    }
}