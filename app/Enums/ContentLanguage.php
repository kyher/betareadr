<?php

namespace App\Enums;

enum ContentLanguage: string
{
    case English = 'en';
    case Spanish = 'es';
    case French = 'fr';

    public function label(): string
    {
        return match ($this) {
            self::English => 'English',
            self::Spanish => 'Español',
            self::French => 'Français',
        };
    }
}
