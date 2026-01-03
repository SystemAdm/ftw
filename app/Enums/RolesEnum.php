<?php

namespace App\Enums;

enum RolesEnum: string
{
    // Site owner
    case OWNER = 'Owner';
    case BOARD_CHAIRMAN = 'Board Chairman';

    // Board members, site leaders
    case BOARD_MEMBER = 'Board Member';
    case INSTRUCTOR = 'Instructor';

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
        return trans('pages.roles.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::OWNER, self::BOARD_CHAIRMAN, self::BOARD_MEMBER => 'red',
            self::ADMIN => 'orange',
            self::INSTRUCTOR => 'purple',
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
        return in_array($this, [self::OWNER, self::BOARD_MEMBER, self::BOARD_CHAIRMAN, self::ADMIN]);
    }
}
