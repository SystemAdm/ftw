<?php

namespace App\events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $eventId;

    public array $reserved;

    public array $attendees;

    public array $inside;

    public function __construct(int $eventId, array $reserved, array $attendees, array $inside)
    {
        $this->eventId = $eventId;
        $this->reserved = $reserved;
        $this->attendees = $attendees;
        $this->inside = $inside;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('events.'.$this->eventId)];
    }

    public function broadcastAs(): string
    {
        return 'EventUpdated';
    }
}
