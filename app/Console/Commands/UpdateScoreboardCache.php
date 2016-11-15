<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\UpdateScoreboardCache as UpdateScoreboardCacheJob;
use App\Currency\CurrencyChannels;

class UpdateScoreboardCache extends Command
{
    use DispatchesJobs, CurrencyChannels;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:update-scoreboard-cache {channel? : The channel to update the scoreboard cache for.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the scoreboard cache.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $channelStr = $this->argument('channel');

        if ($channelStr) {
            if (! $channel = $this->getChannel($channelStr)) {
                return $this->info("Channel '{$channelStr}' was not found.");
            }

            $this->info("Updating scoreboard cache for channel '{$channelStr}'.");
            return $this->dispatch(new UpdateScoreboardCacheJob($channel));
        }

        $channels = $this->getActiveCurrencyChannels();

        if ($channels->isEmpty()) {
            return $this->info('No currency channels active.');
        }

        foreach ($channels as $channel) {
            $this->info("Scoreboard cache has been updated for channel '{$channel->name}'.");
            $this->dispatch(new UpdateScoreboardCacheJob($channel));
        }
    }
}
