<?php

namespace App\Events;

use App\Events\Event;
use App\Channel;
use Illuminate\Support\Collection;

class NewFollower extends Event
{
    /**
     * @var Channel
     */
    public $channel;

    /**
     * @var Collection
     */
    public $followers;

    /**
     * Create a new event instance.
     *
     * @param Channel $channel
     * @param array $followers
     *
     */
    public function __construct(Channel $channel, $followers)
    {
        $this->channel = $channel;
        $this->followers = collect($followers);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $string = '';

        if ($this->channel->getSetting('followers.display-alert-in-chat', false) === true) {
            $names = $this->followers->implode('display_name', ', ');

            $defaultString = "{{ followers }}, thanks for the follow" . ($this->followers->count() > 1 ? 's.' : '.');
            $string = $this->channel->getSetting('followers.welcome-msg', $defaultString);
            $string = preg_replace('/{{\s?followers\s?}}/', $names, $string);
        }


        return [
            'response' => $string
        ];
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
