<?php

namespace App\Policies;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if (RolesEnum::userIsGlobalAdmin($user)) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // For crew index, they should be able to see it if they are in any team
        // This is handled by the 'crew' middleware mostly, but for the policy:
        return $user->teams()->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Event $event): bool
    {
        // Public viewing is handled in the controller (published status)
        // This 'view' in policy usually refers to the management/show view in Crew/Admin
        return $user->teams->contains('id', $event->team_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->teams()->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event): bool
    {
        return $user->teams->contains('id', $event->team_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Event $event): bool
    {
        return $user->teams->contains('id', $event->team_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Event $event): bool
    {
        // Only global admins can restore (handled in before())
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        // Only global admins can force delete (handled in before())
        return false;
    }

    /**
     * Determine whether the user can signup for the event.
     */
    public function signup(User $user, Event $event): bool
    {
        // Basic logic: must be published
        if ($event->status !== 'published') {
            return false;
        }

        // Other logic like age, seats, etc. are currently in the controller.
        // We could move them here if we want the policy to be the single source of truth for "can".
        return true;
    }
}
