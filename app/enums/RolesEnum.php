<?php

namespace App\enums;

enum RolesEnum: string
{
    // Site owner
    case OWNER = 'Owner';

    // Board members, site leaders
    case BOARD_MEMBER = 'Board Member';

    // Administrators, site managers
    case ADMIN = 'Admin';

    // Moderators
    case MODERATOR = 'Moderator';

    // Crew, staff members
    case CREW = 'Crew';

    // Members, paid users
    case MEMBER = 'Member';

    // Guests, participating at events
    case GUEST = 'Guest';

    // Guardians, parents of minors
    case GUARDIAN = 'Guardian';

    // Visitors, just visiting the site
    case VISITOR = 'Visitor';

    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Site Owner',
            self::BOARD_MEMBER => 'Board members, site leaders',
            self::ADMIN => 'Administrators, site managers',
            self::MODERATOR => 'Moderators',
            self::CREW => 'Crew, staff members',
            self::MEMBER => 'Members, paid users',
            self::GUEST => 'Guests, participating at events',
            self::GUARDIAN => 'Guardians, parents of minors',
            default => 'Visitors, just visiting the site',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::OWNER, self::BOARD_MEMBER => 'red',
            self::ADMIN => 'orange',
            self::MODERATOR => 'amber',
            self::CREW => 'yellow',
            self::MEMBER => 'blue',
            self::GUEST => 'green',
            self::GUARDIAN => 'cyan',
            default => 'white',
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this, [self::OWNER, self::BOARD_MEMBER, self::ADMIN]);
    }
}
