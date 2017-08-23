<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Channel;
use App\Contracts\Repositories\StreamRepository;
use App\Support\ScoreboardCache;

class CurrencyController extends Controller
{
    /**
     * @var ChatterRepository
     */
    private $chatterRepository;

    /**
     * @param Request $request
     * @param ChatterRepository $chatterRepository
     */
    public function __construct(Request $request)
    {
        $this->middleware(['featureDetection:currency']);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\View\View
     */
    public function scoreboard(Request $request, Channel $channel, StreamRepository $streamRepo, ScoreboardCache $scoreboardCache)
    {
        $data = [
            'username'  => strtolower($request->get('username')),
            'chatter'   => '{}',
            'streaming' => (bool) $streamRepo->findIncompletedStream($channel)
        ];

        if ($data['username'] && $chatter = $scoreboardCache->findByHandle($channel, $data['username'])) {
            $data['chatter'] = json_encode($chatter);
        }

        $data['status'] = (bool) $channel->getSetting('currency.status');
        $data['scoreboard'] = json_encode($scoreboardCache->paginate($request->get('page', 1))->allForChannel($channel));

        return view('scoreboard', $data);
    }
}
