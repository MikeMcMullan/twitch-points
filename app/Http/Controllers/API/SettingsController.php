<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\MessageBag;
use Illuminate\Contracts\Validation\ValidationException;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Channel;
use App\Support\NamedRankings;

class SettingsController extends Controller
{
    /**
     *
     */
    public function __construct(Request $request)
    {
        $this->middleware(['jwt.auth', 'auth.api']);
        $this->channel = $request->route()->getParameter('channel');
    }

    /**
     * Update settings.
     */
    public function update(Request $request, Dispatcher $events, Channel $channel)
    {
        $newSettings = $request->except("/{$channel->slug}/settings");
        $errorBag = new MessageBag();

        $rules = [
            'active'                => 'required|boolean_real',
            'title'                 => 'required|min:2|max:20',
            'rank-mods'             => 'required|boolean_real',
            'bot.username'          => 'required|max:25',
            'bot.password'          => 'required|size:36',

            'currency.name'         => 'required|min:2|max:15',
            'currency.interval'     => 'required|integer|min:1|max:60',
            'currency.awarded'      => 'required|integer|min:1|max:1000',
            'currency.sync-status'  => 'required|boolean_real',
            'currency.keyword'      => 'required|regex:/^!?\w{2,20}$/',
            'currency.status'       => 'required|boolean_real',

            'giveaway.ticket-cost'  => 'required|integer|min:1|max:1000',
            'giveaway.ticket-max'   => 'required|integer|min:1|max:100',
            'giveaway.started-text' => 'required|max:250',
            'giveaway.stopped-text' => 'required|max:250',
            'giveaway.keyword'      => 'required|regex:/^!?\w{2,20}$/',
            'giveaway.use-tickets'  => 'required|boolean_real',

            'followers.alert'       => 'required|boolean_real',
            'followers.welcome_msg' => 'max:140',
        ];

        $toValidate = [];

        foreach ($newSettings as $setting => $value) {
            if (! isset($rules[$setting])) {
                $errorBag->add($setting, 'Invalid Setting');
            } else {
                array_push($toValidate, $setting);
            }
        }

        $validator = \Validator::make(
            array_only($newSettings, $toValidate),
            array_only($rules, $toValidate)
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $setting => $msg) {
                $errorBag->add($setting, $msg);
            }
        }

        if (! $errorBag->isEmpty()) {
            throw new ValidationException($errorBag);
        }

        foreach ($newSettings as $setting => $value) {
            $events->fire("settings.updated.{$setting}", [
                'channel' => $channel,
                'old_setting' => $this->channel->getSetting($setting),
                'new_setting' => $value
            ]);
        }

        $this->channel->setSetting($newSettings);

        return response()->json($newSettings, 200);
    }

    public function updateNamedRankings(Request $request, Dispatcher $events, Channel $channel)
    {
        try {
            $rankings = new NamedRankings($channel);
            $rankings->clearRankings();

            foreach ($request->input('named-rankings', []) as $rank) {
                $rankings->addRank($rank['name'], $rank['min'], $rank['max']);
            }

            $rankings->save();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Bad Request',
                'code'  => 400,
                'message' => $e->getMessage()
            ], 400);
        }

    }
}
