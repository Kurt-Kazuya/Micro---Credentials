<?php

namespace App\Support;

use App\Models\User;
use Carbon\Carbon;

/**
 * UserPresenter — turns an authenticated User model into the plain
 * profile object every Blade view expects as $user.
 *
 * The views were built against simple objects (->name, ->role,
 * ->avatar_url, ...), so controllers pass these presenters instead of the
 * raw Eloquent model (whose ->role would resolve to the Role relation).
 */
class UserPresenter
{
    /**
     * Shape used by all Student_* pages (topbar + Student_Profile).
     */
    public static function student(User $u): object
    {
        return (object) [
            'id'            => $u->id,
            'name'          => $u->name,
            'role'          => $u->displayRole(),
            'phone'         => $u->phone,
            'email'         => $u->email,
            'joined_at'     => $u->created_at instanceof Carbon ? $u->created_at : Carbon::parse($u->created_at),
            'location'      => $u->location,
            'avatar_url'    => $u->avatar_url,
            'about'         => $u->about,
            'date_of_birth' => self::prettyDate($u->date_of_birth),
            'gender'        => $u->gender,
            'education'     => $u->education,
            'bio'           => $u->bio,
            'language'      => $u->language ?? 'English',
            'timezone'      => $u->timezone ?? 'Asia/Manila',
            'user_code'     => $u->user_code,
            'username'      => $u->username,
            'created_at'    => $u->created_at,
            'is_active'     => $u->is_active,
        ];
    }

    /**
     * Shape used by all Faculty_* pages (topbar + Faculty_Profile).
     */
    public static function faculty(User $u): object
    {
        $joined = $u->created_at instanceof Carbon ? $u->created_at : Carbon::parse($u->created_at);

        return (object) [
            'id'             => $u->id,
            'name'           => $u->name,
            'role'           => $u->displayRole(),
            'phone'          => $u->phone,
            'email'          => $u->email,
            'joined'         => $joined->format('M j, Y'),
            'location'       => $u->location,
            'birth_date_raw' => self::rawDate($u->date_of_birth),
            'birth_date'     => self::prettyDate($u->date_of_birth),
            'gender'         => $u->gender,
            'education'      => $u->education,
            'bio'            => $u->bio,
            'about'          => $u->about,
            'language'       => $u->language ?? 'English',
            'timezone'       => $u->timezone ?? '(GMT + 8:00) Asia/Manila',
            'avatar_url'     => $u->avatar_url,
            'user_code'      => $u->user_code,
            'created_at'     => $u->created_at,
            'is_active'      => $u->is_active,
        ];
    }

    /**
     * Shape used by the Admin_Profile page.
     */
    public static function admin(User $u): object
    {
        return (object) [
            'id'         => $u->id,
            'name'       => $u->name,
            'role'       => $u->displayRole(),
            'phone'      => $u->phone,
            'email'      => $u->email,
            'location'   => $u->location,
            'about'      => $u->about,
            'bio'        => $u->bio,
            'avatar_url' => $u->avatar_url,
            'user_code'  => $u->user_code,
            'created_at' => $u->created_at,
            'is_active'  => $u->is_active,
        ];
    }

    /**
     * 'F j, Y' (e.g. "October 12, 2003") for display; null-safe.
     */
    public static function prettyDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('F j, Y');
        } catch (\Throwable $e) {
            return $value;
        }
    }

    /**
     * 'Y-m-d' for <input type="date">; null-safe.
     */
    public static function rawDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return $value;
        }
    }
}
