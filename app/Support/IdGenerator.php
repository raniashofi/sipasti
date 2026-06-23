<?php

namespace App\Support;

use Illuminate\Support\Str;

class IdGenerator
{
    private const MAX_LENGTH = 36;

    public static function make(string $prefix, bool $withDate = false): string
    {
        $parts = [strtoupper($prefix)];

        if ($withDate) {
            $parts[] = now()->format('Ymd');
        }

        $base = implode('-', $parts);
        $randomLength = self::MAX_LENGTH - strlen($base) - 1;
        $random = substr(str_replace('-', '', (string) Str::uuid()), 0, $randomLength);

        return $base . '-' . $random;
    }

    public static function userPrefix(?string $role): string
    {
        return match ($role) {
            'admin_helpdesk' => 'USR-HD',
            'tim_teknis' => 'USR-TIM',
            'pimpinan' => 'USR-PIM',
            'super_admin' => 'USR-SUPER',
            default => 'USR-OPD',
        };
    }
}
