<?php

namespace App\enums;

enum PostalCodeVisibility: string
{
    case PostalCode = 'postalcode';
    case City = 'city';
    case Municipality = 'municipality';
    case Country = 'country';
    case Off = 'off';

    public function label(): string
    {
        return match ($this) {
            self::PostalCode => 'Postal code',
            self::City => 'City',
            self::Municipality => 'Municipality',
            self::Country => 'Country',
            self::Off => 'Hidden',
        };
    }
}
