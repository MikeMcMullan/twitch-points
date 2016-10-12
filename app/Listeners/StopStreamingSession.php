<?php

namespace App\Listeners;

use App\Events\ChannelStoppedStreaming;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Contracts\Repositories\TrackSessionRepository;

class StopStreamingSession
{
    /**
     * @var TrackSessionRepository
     */
    protected $sessionRepo;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(TrackSessionRepository $sessionRepo)
    {
        $this->sessionRepo = $sessionRepo;
    }

    /**
     * Handle the event.
     *
     * @param  ChannelStoppedStreaming  $event
     * @return void
     */
    public function handle(ChannelStoppedStreaming $event)
    {
        $session = $this->sessionRepo->findIncompletedSession($event->channel);

        if ($session) {
            return $this->sessionRepo->end($session);
        }
    }
}