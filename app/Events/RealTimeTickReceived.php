<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RealTimeTickReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The raw tick payload from EODHD.
     *
     * @var array
     */
    public array $tick;

    /**
     * Create a new event instance.
     *
     * @param  array  $tick
     * @return void
     */
    public function __construct(array $tick)
    {
        $this->tick = $tick;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        // Public channel — every client listening on "ticks" will receive this
        return new Channel('ticks');
    }

    /**
     * Broadcast the raw tick payload as the event data.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return $this->tick;
    }

    /**
     * Override the event name so the client sees "RealTimeTickReceived"
     * instead of the full class name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'RealTimeTickReceived';
    }
}
