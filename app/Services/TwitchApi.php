<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Log\Writer;
use App\Exceptions\InvalidChannelException;

class TwitchApi
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var CacheRepository
     */
    private $cache;

    /**
     * @var ConfigRepository
     */
    private $config;

    /**
     * @var Writer
     */
    private $logger;

    /**
     * @param CacheRepository $cache
     */
    public function __construct(CacheRepository $cache, ConfigRepository $config, Writer $logger)
    {
        $this->httpClient = $this->setupHttpClient();
        $this->cache = $cache;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @return Client
     */
    private function setupHttpClient()
    {
        $client = new Client([
            'headers' => [
                'Accept' => 'application/vnd.twitchtv.v5+json',
                'Client-ID' => config('twitch.credentials.client_id')
            ]
        ]);

        return $client;
    }

    /**
     * Get a users ID by their username.
     *
     * @param  string $username
     * @return int
     */
    public function getUserIDByName($username)
    {
        $response = $this->httpClient->get('https://api.twitch.tv/kraken/users?login=' . $username);

        $json = json_decode($response->getBody());

        if ($json->_total === 0) {
            return false;
        }

        return (int) $json->users[0]->_id;
    }

    /**
     * Get the chat user list from twitch.
     *
     * @param $channel
     * @return array
     */
    public function chatList($channel)
    {
        $attempts = 1;
        $stop = false;

        // Sometimes the tmi server returns an error, we'll try multiple times
        // before giving up.
        $this->logger->info('Trying to get chat list.', ['channel' => $channel]);

        while ($stop === false) {
            try {
                $this->logger->info(sprintf('Attempt: #%d', $attempts), ['channel' => $channel]);
                $response = $this->httpClient->request('GET', sprintf($this->config->get('twitch.chat_list_api'), $channel));
                $this->logger->info(sprintf('Chat list was obtained, took %d attempts.', $attempts), ['channel' => $channel]);
                $stop = true;
            } catch (\GuzzleHttp\Exception\ServerException $e) {
                if ($attempts > 5) {
                    $this->logger->error('Failed to get chat list.', ['channel' => $channel]);
                    $stop = true;
                }

                sleep(0.5);
                $attempts += 1;
            }
        }

        return $this->parseChatList((string) $response->getBody());
    }

    /**
     * Parse the json chat list.
     *
     * @param $jsonString
     * @return array
     */
    private function parseChatList($jsonString)
    {
        $json = json_decode($jsonString, true);

        return [
            'chatters' => array_merge(
                $json['chatters']['staff'],
                $json['chatters']['admins'],
                $json['chatters']['global_mods'],
                $json['chatters']['viewers']
            ),

            'moderators' => $json['chatters']['moderators']
        ];
    }

    /**
     * Fetch channel info from the api.
     *
     * @param array    $channelIDs   An array of channel IDs.
     * @return mixed
     */
    public function getStream($channelIDs)
    {
        try {
            $response = $this->httpClient->request('GET', 'https://api.twitch.tv/kraken/streams?channel=' . implode(',', $channelIDs) . '&_nocachetp=' . time());
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            $this->logger->error('Invalid channel.', ['channel' => $channel]);
            throw new InvalidChannelException($channel);
        } catch (ServerException $e) {
            $this->logger->error('Unable to get stream, twitch api error.');
            throw new \Exception('Unable to get stream, twitch api error.');
        }
    }

    /**
     * Get a users information from the twitch api by their id.
     *
     * @param  string $userId Twitch User ID]
     * @return mixed
     */
    public function getUserById($userId)
    {
        try {
            $response = $this->httpClient->request('GET', 'https://api.twitch.tv/helix/users?id=' . $userId);

            return array_get(json_decode($response->getBody(), true), 'data.0');
        } catch (ServerException $e) {
            $this->logger->error('Unable to get user, twitch api error.');
            throw new \Exception('Unable to get user, twitch api error.');
        }
    }

    /**
     * Get a users information from the twitch api by their username.
     *
     * @param  string $userId Twitch User ID]
     * @return mixed
     */
    public function getUserByUsername($username)
    {
        try {
            $response = $this->httpClient->request('GET', 'https://api.twitch.tv/helix/users?login=' . $username);

            return array_get(json_decode($response->getBody(), true), 'data.0');
        } catch (ServerException $e) {
            $this->logger->error('Unable to get user, twitch api error.');
            throw new \Exception('Unable to get user, twitch api error.');
        }
    }

    /**
     * Get users information from the twitch api by their usernames.
     *
     * @param  string $userId Twitch User ID]
     * @return mixed
     */
    public function getUsersByUsername($usernames)
    {
        try {
            $query = '?login=' . implode('&login=', $usernames);

            $response = $this->httpClient->request('GET', 'https://api.twitch.tv/helix/users' . $query);

            return array_get(json_decode($response->getBody(), true), 'data');
        } catch (ServerException $e) {
            $this->logger->error('Unable to get user, twitch api error.');
            throw new \Exception('Unable to get user, twitch api error.');
        }
    }
}
