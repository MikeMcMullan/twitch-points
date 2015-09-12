<?php namespace App\Console\Commands;

use App\Commands\DownloadChatList;
use App\Contracts\Repositories\TrackSessionRepository;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UpdatePoints extends Command {

	use DispatchesCommands;

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'points:update';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update chat list for a channel.';

	/**
	 * @var TrackPointsSession
	 */
	private $pointsSession;

	/**
	 * Create a new command instance.
	 *
	 * @param TrackSessionRepository $pointsSession
	 */
	public function __construct(TrackSessionRepository $pointsSession)
	{
		parent::__construct();
		$this->pointsSession = $pointsSession;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$startTime = microtime(true);
		$sessions = $this->pointsSession->allUncompletedSessions();

		try {
			foreach ($sessions as $session) {
				$this->dispatch(new DownloadChatList($session->channel));
			}
		} catch (\Exception $e) {
			dd($e->getFile(), $e->getLine());
		}

		$end = microtime(true) - $startTime;
		\Log::info(sprintf('Updated Points in %s seconds', $end));
		$this->info(sprintf('Updated Points in %s seconds', $end));
	}
}
