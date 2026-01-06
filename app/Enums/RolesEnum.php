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

    public function isGlobalAdmin(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN]);
    }

    public function isAdmin(): bool
    {
        return $this->isGlobalAdmin() || in_array($this, [self::BOARD_MEMBER, self::BOARD_CHAIRMAN, self::INSTRUCTOR]);
    }

    /**
     * Check if a user has a global admin role (Owner or Admin).
     */
    public static function userIsGlobalAdmin($user): bool
    {
        if (! $user) {
            return false;
        }

        return \DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_id', $user->id)
            ->where('model_type', get_class($user))
            ->whereIn('roles.name', [self::OWNER->value, self::ADMIN->value])
            ->where('model_has_roles.team_id', 0)
            ->exists();
    }
}
