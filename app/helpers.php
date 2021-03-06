<?php

/**
 * Add protocol and port to a domain name.
 *
 * @param  String $domain   Domain name without protocol, port or path.
 * @param  String $protocol If not null protocal will be guessed based on the request.
 * @return String
 */
function makeDomain($domain, $protocol = null)
{
    $request  = app(\Illuminate\Http\Request::class);
    $port     = in_array($request->getPort(), [80, 443]) ? '' : ':' . $request->getPort();

    if ($protocol === null) {
        $protocol = config('app.secure') ? 'https://' : 'http://';
    }

    return $protocol . $domain . $port;
}

/**
 * Check if we are in the api based on the host name.
 * @param $host
 *
 * @return bool
 */
function isApi($host)
{
    return substr($host, 0 , 3) === 'api';
}

/**
 * Display time in days and hours.
 *
 * @param $minutes
 *
 * @return string
 */
function presentTimeOnline($minutes)
{
    if ($minutes >= 1440) {
        $minutesInDay = 60 * 24;
        $days = floor($minutes / $minutesInDay);
        $hours = floor(floor($minutes - ($days * $minutesInDay)) / 60);

        $output =  $days . ' days';

        if ($hours > 0) {
            $output .= ', ' . $hours . ' hours';
        }

        return $output;
    }

    if ($minutes > 60 && $minutes < 1440) {
        $hours = floor($minutes / 60);

        return $hours . ' hours';
    }

    return $minutes . ' minutes';
}

/**
 * Set the time the points system for a channel was last updated.
 *
 * @param Channel $channel
 * @param Carbon $time
 *
 * @return mixed
 */
function setLastUpdate(App\Channel $channel, Carbon\Carbon $time)
{
    return Cache::forever("{$channel->id}:lastUpdate", $time->second(0)->toDateTimeString());
}

/**
 * Get the time the points system was last updated for a channel.
 *
 * Channel $channel
 * @return string
 */
function getLastUpdate(App\Channel $channel)
{
    $lastUpdate = Cache::get("{$channel->id}:lastUpdate");

    if ($lastUpdate) {
        return Carbon\Carbon::parse($lastUpdate);
    }
}

function getUserFromRedis($username)
{
    if (strlen($username) === 0) {
        return null;
    }

    $redis = app('redis');

    $userId = $redis->hget('twitch:usernameIdMap', $username);

    if ($userId) {
        $user = $redis->hget('twitch:chatUsers', $userId);

        if ($user) {
            $user = json_decode($user, true);
            $user['twitch_id'] = $user['user_id'];
            return $user;
        }
    }

    return null;
}

function addUserToRedis($user)
{
    if (! isset($user['username'])) {
        return null;
    }

    $redis = app('redis');

    $exists = $redis->hexists('twitch:usernameIdMap', $user['username']);

    if ($exists === 0) {
        $redis->hset('twitch:usernameIdMap', $user['username'], $user['id']);
    }

    $data = [
        'user_id'       => $user['id'],
        'username'      => $user['username'],
        'display_name'  => $user['display_name'],
        'createdAt'     => time()
    ];

    $redis->hset('twitch:chatUsers', $user['id'], json_encode($data));

    return $data;
}

/**
 * Try to find the display name for a username.
 *
 * @param  string $username
 * @return string
 */
function getDisplayName($username)
{
    $user = getUserFromRedis($username);

    if ($user) {
        return $user['display_name'];
    }

    return $username;
}
