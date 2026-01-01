<?php

namespace App\Enums;

enum BirthdayVisibility: string
{
    case Birthdate = 'birthdate';
    case Birthyear = 'birthyear';
    case Age = 'age';
    case Off = 'off';

    public function label(): string
    {
        return match ($this) {
            self::Birthdate => 'Full birthdate',
            self::Birthyear => 'Birth year only',
            self::Age => 'Age only',
            self::Off => 'Hidden',
        };
    }
}
